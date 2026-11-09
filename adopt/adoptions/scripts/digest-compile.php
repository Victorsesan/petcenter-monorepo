<?php
/** TASK-MANUS-06 START — F3 weekly Pet Alert digest compiler. */
require_once __DIR__ . '/digest-config.php';
$root = dirname(__DIR__, 2);
$searchFile = __DIR__ . '/visitor-saved-searches.json';
$storyFile = __DIR__ . '/verified-rehome-stories.json';
$petRoot = $root . '/adoptions/pets';
function pc_digest_read($file) { $value = @json_decode((string)@file_get_contents($file), true); return is_array($value) ? $value : []; }
function pc_digest_escape($value) { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function pc_digest_pets($root) { $pets=[]; foreach (glob($root . '/*/*/pet.json') ?: [] as $file) { $pet=json_decode((string)@file_get_contents($file),true); if(is_array($pet)) $pets[]=$pet; } return $pets; }
$saved = pc_digest_read($searchFile); $story = pc_digest_read($storyFile); $pets = pc_digest_pets($petRoot);
foreach ($saved as $visitor) {
    $email = filter_var($visitor['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email || !empty($visitor['digest_opt_out'])) continue;
    $filters = is_array($visitor['filters'] ?? null) ? $visitor['filters'] : [];
    $matches = array_filter($pets, function($pet) use ($filters) { foreach(['category','city','size','energy'] as $field) if(!empty($filters[$field]) && strcasecmp((string)($pet[$field]??''),(string)$filters[$field])!==0) return false; return (($pet['status']??'active')==='active'); });
    $items=''; foreach(array_slice($matches,0,10) as $pet) $items.='<li><strong>'.pc_digest_escape($pet['name']??'A pet').'</strong> is looking for a home.</li>'; if(!$items)$items='<li>No new matching pets this week.</li>';
    $storyText=is_array($story)&&!empty($story['title'])?'<p><strong>Verified rehome story:</strong> '.pc_digest_escape($story['title']).'</p>':'';
    $unsubscribe='/adoptions/unsubscribe.php?email='.rawurlencode($email).'&token='.pc_digest_token($email);
    $body='<!doctype html><html><body style="margin:0;border-top:4px solid #ED7624;padding:24px;font-family:Sora,Arial,sans-serif;color:#2c2416"><h1 style="color:#10214b">Your Pet Center digest</h1><ul>'.$items.'</ul>'.$storyText.'<p><a href="'.pc_digest_escape($unsubscribe).'" style="color:#ED7624">Unsubscribe from weekly digests</a></p></body></html>';
    pc_digest_send($email,'Your Pet Center weekly digest',$body);
}
if (PHP_SAPI==='cli') echo "Digest compilation complete.\n";
