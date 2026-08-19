<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

function assistant_reply(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function assistant_products(PDO $db, string $query, int $limit = 3): array
{
    $products = $db->query('SELECT id,name,slug,category,description,details,price,stock,image,featured FROM products WHERE active=1 AND stock>0 ORDER BY featured DESC,id')->fetchAll();
    $query = strtolower($query);
    $tokens = array_values(array_filter(preg_split('/[^a-z0-9]+/', $query) ?: [], static fn(string $word): bool => strlen($word) > 2 && !in_array($word, ['the','and','for','with','that','this','have','show','need','want','please','product','products'], true)));
    foreach ($products as &$product) {
        $haystack = strtolower(implode(' ', [$product['name'],$product['category'],$product['description'],$product['details']]));
        $score = (int)$product['featured'];
        foreach ($tokens as $token) $score += substr_count($haystack, $token) * 4;
        if (str_contains($query, 'mask') && str_contains($haystack, 'mask')) $score += 20;
        if (str_contains($query, 'pressure') && str_contains($haystack, 'pressure')) $score += 20;
        if ((str_contains($query, 'temperature') || str_contains($query, 'thermometer')) && str_contains($haystack, 'thermometer')) $score += 20;
        $product['_score'] = $score;
    }
    unset($product);
    usort($products, static fn(array $a, array $b): int => $b['_score'] <=> $a['_score']);
    $selected = array_slice($products, 0, $limit);
    return array_map(static function(array $product): array {
        return [
            'id'=>(int)$product['id'], 'name'=>$product['name'], 'category'=>$product['category'],
            'description'=>$product['description'], 'price'=>(float)$product['price'], 'stock'=>(int)$product['stock'],
            'image'=>url($product['image']), 'url'=>url('product/'.$product['slug'])
        ];
    }, $selected);
}

function assistant_fallback(string $message, array $products): array
{
    $text = strtolower($message);
    if (preg_match('/\b(enquir|quote|wholesale|bulk|business|speak to|human)\b/', $text)) {
        return ['reply'=>'I can send an enquiry directly to the Lurbrook Health team. Complete the short form below and your message will appear in the admin enquiry inbox.','show_enquiry'=>true,'products'=>[]];
    }
    if (preg_match('/\b(checkout|pay|payment|paypal)\b/', $text)) {
        $count = cart_count();
        return ['reply'=>$count ? "You have {$count} item".($count===1?'':'s')." in your bag. Continue to secure checkout when you are ready; payment is handled by PayPal." : 'Your bag is currently empty. I can help you find a product first, then guide you to secure PayPal checkout.','show_checkout'=>$count>0,'products'=>$count>0?[]:$products];
    }
    if (preg_match('/\b(deliver|delivery|shipping|dispatch|track)\b/', $text)) return ['reply'=>'Lurbrook Health delivers across the United Kingdom. In-stock orders are usually processed within 1–2 working days. Standard delivery is £'.number_format((float)setting('shipping_flat','4.95'),2).' and delivery is free on qualifying orders over £'.number_format((float)setting('shipping_free_over','75'),0).'.','products'=>[]];
    if (preg_match('/\b(returns?|refunds?|cancel|cancellation)\b/', $text)) return ['reply'=>'Eligible unused items may be returned under the Returns & Refund Policy. Sealed PPE and medical consumables normally cannot be returned after the hygiene seal is opened unless faulty. I can open the full policy or send an enquiry for your specific order.','policy_url'=>url('returns-refund-policy'),'show_enquiry'=>true,'products'=>[]];
    if (preg_match('/\b(privacy|personal data|data protection)\b/', $text)) return ['reply'=>'Lurbrook Health uses personal information to process orders, respond to enquiries and operate the website. Payment details are handled securely by PayPal and are not stored by Lurbrook Health. The full Privacy Policy explains your rights and choices.','policy_url'=>url('privacy-policy'),'products'=>[]];
    if (preg_match('/\b(cookie|cookies)\b/', $text)) return ['reply'=>'The Cookie Policy explains the essential and optional technologies used by the website and how you can manage your browser choices.','policy_url'=>url('cookies-policy'),'products'=>[]];
    if (preg_match('/\b(terms|conditions|legal)\b/', $text)) return ['reply'=>'The website Terms & Conditions cover ordering, payment, delivery, cancellations and use of the Lurbrook Health website.','policy_url'=>url('terms-and-conditions'),'products'=>[]];
    if (preg_match('/\b(about|company|lurbrook)\b/', $text)) return ['reply'=>'Lurbrook Health LTD supplies quality PPE, healthcare consumables and medical equipment to businesses, healthcare organisations and consumers across the UK.','policy_url'=>url('about'),'products'=>[]];
    if (preg_match('/\b(phone|email|address|contact)\b/', $text)) return ['reply'=>'You can reach Lurbrook Health at '.setting('email').' or '.setting('phone').'. You can also send a message here and it will go directly into the team’s enquiry inbox.','show_enquiry'=>true,'products'=>[]];
    if (preg_match('/\b(diagnos|symptom|treatment|medicine|dosage|emergency|doctor)\b/', $text)) return ['reply'=>'I can explain products and store information, but I cannot diagnose symptoms or provide medical treatment advice. Please contact a qualified healthcare professional; for an emergency, use the appropriate emergency service.','products'=>[]];
    if (preg_match('/\b(hello|hi|hey|help)\b/', $text) && strlen($text) < 45) return ['reply'=>'Hello! I’m the Lurbrook Health Assistant. I can help you find products, answer delivery and returns questions, guide you to checkout, or send an enquiry to our team.','products'=>[]];
    return ['reply'=>$products ? 'Here are the closest available products I found. Select a product for details or add it to your bag, and I can guide you through checkout.' : 'I could not find a matching product. Try asking about thermometers, blood pressure monitors, PPE or masks, or send an enquiry to our team.','products'=>$products];
}

function assistant_ai(string $message, array $history, array $products, array $config, PDO $db): ?string
{
    $apiKey = trim((string)($config['openai_api_key'] ?: setting('openai_api_key')));
    if ($apiKey === '' || !function_exists('curl_init')) return null;
    $catalogue = $db->query('SELECT name,category,description,details,price,stock FROM products WHERE active=1 ORDER BY category,name')->fetchAll();
    $pages = $db->query('SELECT title,excerpt,content FROM pages WHERE active=1 ORDER BY title')->fetchAll();
    $knowledge = "LIVE STORE FACTS:\nEmail: ".setting('email')."\nPhone: ".setting('phone')."\nStandard delivery: £".setting('shipping_flat','4.95')."\nFree delivery threshold: £".setting('shipping_free_over','75')."\nPayment gateway: PayPal\n\nLIVE PRODUCT CATALOGUE:\n" . json_encode($catalogue, JSON_UNESCAPED_UNICODE) . "\nWEBSITE PAGES AND POLICIES:\n";
    foreach ($pages as $page) $knowledge .= $page['title'].': '.$page['excerpt'].' '.substr(trim(preg_replace('/\s+/', ' ', strip_tags($page['content']))),0,1800)."\n";
    $history = array_slice(array_values(array_filter($history, static fn($item): bool => is_array($item) && isset($item['role'],$item['content']))), -6);
    $conversation = '';
    foreach ($history as $item) $conversation .= (($item['role'] === 'assistant') ? 'Assistant' : 'Customer').': '.substr((string)$item['content'],0,800)."\n";
    $instructions = "You are the Lurbrook Health Assistant, a concise, warm UK medical-supply sales assistant. Answer only from the supplied live catalogue and website information. Help customers compare and locate products, understand stock, delivery, returns, PayPal checkout and contact options. Never invent products, prices, stock, policy, order status or medical claims. Do not diagnose, prescribe or offer treatment advice; for clinical questions advise consulting a qualified healthcare professional. Never claim an enquiry or cart action happened unless the interface confirms it. Keep replies under 100 words and do not use Markdown.\n\n".$knowledge;
    $payload = ['model'=>(string)setting('openai_model',$config['openai_model']),'instructions'=>$instructions,'input'=>$conversation.'Customer: '.$message,'reasoning'=>['effort'=>'none'],'text'=>['verbosity'=>'low'],'max_output_tokens'=>240];
    $request = curl_init('https://api.openai.com/v1/responses');
    curl_setopt_array($request, [CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$apiKey,'Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload)]);
    $raw = curl_exec($request);
    $status = (int)curl_getinfo($request, CURLINFO_HTTP_CODE);
    curl_close($request);
    if (!is_string($raw) || $status < 200 || $status >= 300) return null;
    $response = json_decode($raw, true);
    foreach (($response['output'] ?? []) as $output) foreach (($output['content'] ?? []) as $content) if (($content['type'] ?? '') === 'output_text' && !empty($content['text'])) return trim((string)$content['text']);
    return null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') assistant_reply(['message'=>'Method not allowed'], 405);
if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) assistant_reply(['message'=>'Your session expired. Refresh the page and try again.'], 419);
if (setting('assistant_enabled','1') !== '1') assistant_reply(['message'=>'The assistant is currently unavailable.'], 503);

$now = time();
$_SESSION['assistant_requests'] = array_values(array_filter($_SESSION['assistant_requests'] ?? [], static fn($time): bool => (int)$time > $now - 600));
if (count($_SESSION['assistant_requests']) >= 35) assistant_reply(['message'=>'Please wait a few minutes before sending another message.'], 429);
$_SESSION['assistant_requests'][] = $now;

$action = (string)($_POST['action'] ?? 'chat');
if ($action === 'enquiry') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? 'Assistant enquiry'));
    $message = trim((string)($_POST['message'] ?? ''));
    if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) assistant_reply(['message'=>'Please provide your name, a valid email address and your message.'], 422);
    $stmt = $db->prepare('INSERT INTO messages(name,email,phone,subject,message) VALUES(?,?,?,?,?)');
    $stmt->execute([substr($name,0,120),substr($email,0,190),substr($phone,0,60),substr($subject,0,180),'Sent via Lurbrook Health Assistant: '.substr($message,0,3000)]);
    assistant_reply(['ok'=>true,'reply'=>'Thank you—your enquiry has been sent to the Lurbrook Health team. They can reply using the contact details you provided.']);
}

$message = trim((string)($_POST['message'] ?? ''));
if ($message === '' || strlen($message) > 1200) assistant_reply(['message'=>'Please enter a message of up to 1,200 characters.'], 422);
$history = json_decode((string)($_POST['history'] ?? '[]'), true);
if (!is_array($history)) $history = [];
$products = assistant_products($db, $message);
$fallback = assistant_fallback($message, $products);
$aiReply = assistant_ai($message, $history, $products, $config, $db);
if ($aiReply) $fallback['reply'] = $aiReply;
$fallback['ok'] = true;
$fallback['cart_count'] = cart_count();
$fallback['cart_url'] = url('cart');
$fallback['checkout_url'] = url('checkout');
$fallback['contact_url'] = url('contact');
assistant_reply($fallback);
