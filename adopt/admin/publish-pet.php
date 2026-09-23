<?php
session_start();
header('Content-Type: application/json');

function isAuthenticated() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) return false;
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    $admin_token = isset($m[1]) ? trim($m[1]) : null;
    if (!$admin_token) return false;
    $provided = $_POST['token'] ?? null;
    if ($provided === $admin_token) { $_SESSION['admin_token'] = $provided; return true; }
    return false;
}

if (!isAuthenticated()) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$pets_input = $_POST['pets'] ?? null;
if (!$pets_input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'No pets provided']));
}

$pets = json_decode($pets_input, true);
if (!is_array($pets)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid pets format']));
}

$published_pets = [];
$temp_dir = __DIR__ . '/../adoptions/temp_admin';
$pets_base = __DIR__ . '/../adoptions/pets';

function generatePetId($prefix, $category) {
    global $pets_base;
    $category_folder = $pets_base . "/{$category}";
    $existing = [];
    if (is_dir($category_folder)) {
        foreach (scandir($category_folder) as $entry) {
            if ($entry !== '.' && $entry !== '..' && $entry !== 'index.json') {
                $existing[] = $entry;
            }
        }
    }
    $count = count($existing) + 1;
    // Ensure unique
    do {
        $id = $prefix . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        $count++;
    } while (is_dir($category_folder . '/' . $id));
    return $id;
}

function updateCategoryIndex($category) {
    global $pets_base;
    $category_folder = $pets_base . "/{$category}";
    $pet_ids = [];
    if (!is_dir($category_folder)) return;
    foreach (scandir($category_folder) as $folder) {
        if ($folder === '.' || $folder === '..' || $folder === 'index.json') continue;
        $pet_file = $category_folder . '/' . $folder . '/pet.json';
        if (file_exists($pet_file)) {
            $pet_data = json_decode(file_get_contents($pet_file), true);
            $pet_ids[] = $pet_data['pet_id'];
        }
    }
    file_put_contents($category_folder . '/index.json', json_encode(['pets' => $pet_ids], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// TASK-2 START — Livestocks & Aquariums category prefixes added for new-category support
/* BUGFIX-4 START — reptiles and small-mammals were generating REPTILE-/MAM-
   prefixed pet IDs instead of the documented standard REP-/SM- (confirmed
   against adoption-website-project-summary.md's folder structure and the
   multi-pet carousel's prefix-inference logic on the public site — those
   are the ones actually correct; this generator was the one that was
   wrong). First flagged in Task 2's "Bugs Found Outside Task Scope."
   See the companion fix in delete-pet.php: shortening 'small-mammals' to
   the 2-character 'SM' prefix breaks that file's old fixed first-3-
   characters category-detection trick (it would read "SM-" — including
   the dash — instead of "SM"), so that file's detection logic needed a
   matching, more robust fix alongside this one, not just this file alone. */
$prefix_map = ['dogs' => 'DOG', 'cats' => 'CAT', 'birds' => 'BIRD', 'reptiles' => 'REP', 'small-mammals' => 'SM', 'livestocks' => 'LVS', 'aquariums' => 'AQU'];
/* BUGFIX-4 END */
// TASK-2 END

foreach ($pets as $pet_input) {
    $category = $pet_input['category'] ?? null;
    $name = $pet_input['name'] ?? null;
    $breed = $pet_input['breed'] ?? null;
    
    if (!$category || !$name || !$breed) continue;
    
    $id_prefix = $prefix_map[$category] ?? strtoupper(substr($category, 0, 3));
    $pet_id = generatePetId($id_prefix, $category);
    
    $pet_folder = $pets_base . "/{$category}/{$pet_id}";
    if (!is_dir($pet_folder)) mkdir($pet_folder, 0755, true);
    
    $bulk_folder = $pet_folder . '/bulk_images';
    if (!is_dir($bulk_folder)) mkdir($bulk_folder, 0755, true);
    
    // Handle single image upload token
    $single_image = '';
    $single_token = $pet_input['single_token'] ?? null;
    if ($single_token) {
        $temp_files = glob($temp_dir . '/' . $single_token . '_*');
        if (!empty($temp_files)) {
            $temp_file = $temp_files[0];
            $ext = strtolower(pathinfo($temp_file, PATHINFO_EXTENSION));
            $single_image = 'single_image.' . ($ext ?: 'jpg');
            copy($temp_file, $pet_folder . '/' . $single_image);
            unlink($temp_file);
        }
    }
    
    // Handle bulk images upload tokens
    $bulk_images = [];
    $bulk_tokens = $pet_input['bulk_tokens'] ?? [];
    if (!empty($bulk_tokens)) {
        $counter = 1;
        foreach ($bulk_tokens as $token) {
            $temp_files = glob($temp_dir . '/' . $token . '_*');
            if (!empty($temp_files)) {
                $temp_file = $temp_files[0];
                $ext = strtolower(pathinfo($temp_file, PATHINFO_EXTENSION));
                $filename = 'image' . $counter . '.' . ($ext ?: 'jpg');
                copy($temp_file, $bulk_folder . '/' . $filename);
                unlink($temp_file);
                $bulk_images[] = $filename;
                $counter++;
            }
        }
    }
    
    // Handle video upload token (from chunked/resumable upload via upload-video-chunk.php)
    $video = '';
    $video_token = $pet_input['video_token'] ?? null;
    if ($video_token) {
        $temp_files = glob($temp_dir . '/' . $video_token . '_*');
        if (!empty($temp_files)) {
            $temp_file = $temp_files[0];
            $ext = strtolower(pathinfo($temp_file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v'])) $ext = 'mp4';
            $video = 'pet_video.' . $ext;
            copy($temp_file, $pet_folder . '/' . $video);
            unlink($temp_file);
            @unlink($temp_dir . '/' . $video_token . '.json');
        }
    }
    
    $pet_data = [
        'pet_id'               => $pet_id,
        'id'                   => $pet_id,
        'name'                 => $pet_input['name'],
        'category'             => $category,
        'species'              => $pet_input['species'] ?? ucfirst(rtrim($category, 's')),
        'breed'                => $pet_input['breed'],
        'gender'               => $pet_input['gender']             ?? '',
        'age_years'            => floatval($pet_input['age_years'] ?? 0),
        'size'                 => $pet_input['size']               ?? '',
        'adoption_fee'         => floatval($pet_input['adoption_fee'] ?? 0),
        'description_short'    => $pet_input['description_short']  ?? '',
        'description_full'     => $pet_input['description_full']   ?? '',
        'health_info'          => $pet_input['health_info']        ?? '',
        'personality'          => !empty($pet_input['personality'])
            ? array_values(array_filter(array_map('trim', explode(',', $pet_input['personality']))))
            : [],
        'compatible_kids'      => $pet_input['compatible_kids']        ?? 'unknown',
        'compatible_dogs'      => $pet_input['compatible_dogs']        ?? 'unknown',
        'compatible_cats'      => $pet_input['compatible_cats']        ?? 'unknown',
        'compatible_other_pets'=> $pet_input['compatible_other_pets']  ?? 'unknown',
        'house_trained'        => $pet_input['house_trained']          ?? 'no',
        'spayed_neutered'      => $pet_input['spayed_neutered']        ?? 'no',
        'vaccinated'           => $pet_input['vaccinated']             ?? 'no',
        'status'               => 'Available',
        'single_image'         => $single_image,
        'single_image_url'     => $single_image ? "/adoptions/pets/{$category}/{$pet_id}/{$single_image}" : '',
        'bulk_images'          => $bulk_images,
        'bulk_images_url'      => !empty($bulk_images) ? "/adoptions/pets/{$category}/{$pet_id}/bulk_images/" : '',
        'video'                => $video,
        'video_url'            => $video ? "/adoptions/pets/{$category}/{$pet_id}/{$video}" : '',
        'published_date'       => date('Y-m-d H:i:s'),
    ];
    
    file_put_contents($pet_folder . '/pet.json', json_encode($pet_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    updateCategoryIndex($category);
    
    $published_pets[] = ['pet_id' => $pet_id, 'name' => $name];
}

if (empty($published_pets)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'No valid pets to publish']));
}

echo json_encode(['success' => true, 'message' => count($published_pets) . ' pets published successfully', 'pets' => $published_pets]);
?>
