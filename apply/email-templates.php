<?php
/**
 * email-templates.php
 *
 * Shared HTML email builders for the adoption pipeline. Included by both
 * adoption-application.php (on initial submission -> PENDING email) and
 * update-adoption.php (on admin status change -> APPROVED / REJECTED email,
 * including re-reversals — this file always sends whatever the CURRENT
 * status is, so an admin flipping APPROVED -> REJECTED later naturally
 * triggers a fresh, correct email with no extra logic needed).
 *
 * All three templates share the same visual language as the live
 * /adoptions/status/ dashboard so the emails and the website never say
 * different things.
 */

/**
 * Resolve a pet's category folder from its ID prefix, matching the exact
 * logic used on the frontend (adoption-application.html / status.html):
 * split on '-' and take the first segment, e.g. "BIRD-00001" -> "BIRD",
 * "SM-00002" -> "SM". This is deliberately NOT the same as the 3-letter
 * substr() logic in update-adoption.php's getPetCategory(), because that
 * older helper mis-resolves "SM-00002" (substr(0,3) => "SM-", not "SM").
 */
function emailPetCategoryFromId($pet_id) {
    $map = [
        'DOG'  => 'dogs',
        'CAT'  => 'cats',
        'BIRD' => 'birds',
        'REP'  => 'reptiles',
        'SM'   => 'small-mammals',
    ];
    $prefix = strtoupper(strtok($pet_id, '-'));
    return $map[$prefix] ?? null;
}

/**
 * Load a pet.json from disk given its ID. $adoptionsPetsBase should be the
 * absolute path to the /adoptions/pets/ folder (site root, sibling to /apply/).
 */
function emailLoadPet($pet_id, $adoptionsPetsBase) {
    if (!$pet_id) return null;
    $category = emailPetCategoryFromId($pet_id);
    if (!$category) return null;
    $path = rtrim($adoptionsPetsBase, '/\\') . "/{$category}/{$pet_id}/pet.json";
    if (!file_exists($path)) return null;
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : null;
}

/**
 * Build the full list of pets applied for on one application: the primary
 * pet (already embedded in application.json) plus every pet referenced in
 * additional_pet_ids[], each resolved live from its own pet.json so the
 * email always reflects current pet details (name, image, breed, fee).
 *
 * Returns an array of normalised pet rows:
 *   ['name'=>, 'breed'=>, 'age'=>, 'image_url'=>, 'fee'=>, 'pet_id'=>]
 */
function emailBuildPetList($application, $adoptionsPetsBase) {
    $pets = [];

    // Primary pet — prefer the live pet.json (more current) but fall back
    // to what was captured on application.json at submission time.
    $primaryId = $application['pet_id'] ?? '';
    $livePrimary = $primaryId ? emailLoadPet($primaryId, $adoptionsPetsBase) : null;

    if ($livePrimary) {
        $ageYears = $livePrimary['age_years'] ?? '';
        $pets[] = [
            'pet_id'    => $primaryId,
            'name'      => $livePrimary['name'] ?? ($application['pet_name'] ?? 'Pet'),
            'breed'     => $livePrimary['breed'] ?? ($application['pet_breed'] ?? ''),
            'age'       => $ageYears !== '' ? ($ageYears . ($ageYears == 1 ? ' year' : ' years')) : ($application['pet_age'] ?? ''),
            'image_url' => $livePrimary['single_image_url'] ?? ($application['pet_image_url'] ?? ''),
            'fee'       => isset($livePrimary['adoption_fee']) ? $livePrimary['adoption_fee'] : ($application['adoption_fee'] ?? ''),
        ];
    } else {
        $pets[] = [
            'pet_id'    => $primaryId,
            'name'      => $application['pet_name']    ?? 'Pet',
            'breed'     => $application['pet_breed']   ?? '',
            'age'       => $application['pet_age']     ?? '',
            'image_url' => $application['pet_image_url'] ?? '',
            'fee'       => $application['adoption_fee'] ?? '',
        ];
    }

    // Additional pets — resolved entirely from their own pet.json since
    // application.json only stores their IDs.
    $additionalIds = isset($application['additional_pet_ids']) && is_array($application['additional_pet_ids'])
        ? $application['additional_pet_ids']
        : [];

    foreach ($additionalIds as $petId) {
        if (!$petId) continue;
        $pet = emailLoadPet($petId, $adoptionsPetsBase);
        if (!$pet) {
            // Pet record missing/deleted — still list the ID so the user
            // isn't left wondering what happened to their other pet(s).
            $pets[] = [
                'pet_id'    => $petId,
                'name'      => $petId,
                'breed'     => '',
                'age'       => '',
                'image_url' => '',
                'fee'       => '',
            ];
            continue;
        }
        $ageYears = $pet['age_years'] ?? '';
        $pets[] = [
            'pet_id'    => $petId,
            'name'      => $pet['name']  ?? $petId,
            'breed'     => $pet['breed'] ?? '',
            'age'       => $ageYears !== '' ? ($ageYears . ($ageYears == 1 ? ' year' : ' years')) : '',
            'image_url' => $pet['single_image_url'] ?? '',
            'fee'       => isset($pet['adoption_fee']) ? $pet['adoption_fee'] : '',
        ];
    }

    return $pets;
}

/**
 * Render the "Pets You Applied For" section shared across all three emails.
 * Works for a single pet or several — each gets its own image/name/breed/fee row.
 */
function emailRenderPetsSection($pets) {
    if (empty($pets)) return '';

    $rows = '';
    foreach ($pets as $p) {
        $img = $p['image_url'] ?: '';
        $feeVal = $p['fee'];
        $feeDisplay = ($feeVal !== '' && $feeVal !== null && is_numeric($feeVal))
            ? '$' . number_format((float)$feeVal, 2)
            : ($feeVal !== '' ? htmlspecialchars($feeVal) : '&mdash;');

        $imgCell = $img
            ? "<td style='width:72px;padding-right:14px;vertical-align:top;'><img src='" . htmlspecialchars($img) . "' alt='" . htmlspecialchars($p['name']) . "' style='width:64px;height:64px;object-fit:cover;border-radius:10px;display:block;background:#f1f5f9;'></td>"
            : "<td style='width:72px;padding-right:14px;vertical-align:top;'><div style='width:64px;height:64px;border-radius:10px;background:#f1f5f9;'></div></td>";

        $rows .= "<tr><td style='padding:12px 0;border-bottom:1px solid #e5e7eb;'>"
                . "<table width='100%' cellpadding='0' cellspacing='0' border='0'><tr>"
                . $imgCell
                . "<td style='vertical-align:top;'>"
                . "<p style='margin:0;font-size:15px;font-weight:700;color:#1a1a1a;'>" . htmlspecialchars($p['name']) . "</p>"
                . "<p style='margin:2px 0 0;font-size:13px;color:#6b7280;'>" . htmlspecialchars(trim(($p['breed'] ?: '') . (($p['breed'] && $p['age']) ? ' &middot; ' : '') . ($p['age'] ?: ''), " \xC2\xB7")) . "</p>"
                . "<p style='margin:4px 0 0;font-size:13px;color:#1a1a1a;font-weight:600;'>Adoption Fee: {$feeDisplay}</p>"
                . ($p['pet_id'] ? "<p style='margin:2px 0 0;font-size:11px;color:#9ca3af;font-family:monospace;'>" . htmlspecialchars($p['pet_id']) . "</p>" : '')
                . "</td></tr></table>"
                . "</td></tr>";
    }

    $heading = count($pets) > 1 ? 'Pets You Applied For' : 'Pet You Applied For';

    return "<tr><td style='padding:0 0 30px;'>"
         . "<h3 style='margin:0 0 12px;font-size:16px;font-weight:700;color:#1a1a1a;'>{$heading}</h3>"
         . "<table width='100%' cellpadding='0' cellspacing='0' border='0' style='background:#f9fafb;border-radius:12px;padding:4px 20px;border:1px solid #f1f5f9;'>{$rows}</table>"
         . "</td></tr>";
}

/**
 * Shared footer block — identical links to the live site footer
 * (status.html / index.html), used across all three email templates.
 */
function emailFooterBlock($user_email, $sid, $status_url) {
    return <<<HTML
      <tr><td style="background:#f8fafc;border-radius:12px;padding:40px 20px;text-align:center;">
        <p style="margin:0 0 15px;font-size:14px;color:#1a1a1a;font-weight:700;">Thank you for giving a rescue pet a second chance.</p>
        <p style="margin:0 0 15px;font-size:12px;color:#64748b;line-height:1.6;"><strong>Pet Center</strong><br>
          <a href="https://docs.petcenterco.com/privacy-policy/" style="color:#1a1a1a;text-decoration:none;">Privacy Policy</a> |
          <a href="https://docs.petcenterco.com/terms-of-service/" style="color:#1a1a1a;text-decoration:none;">Terms of Service</a> |
          <a href="https://docs.petcenterco.com/returns-refunds/" style="color:#1a1a1a;text-decoration:none;">Returns &amp; Refunds</a> |
          <a href="https://docs.petcenterco.com/contact-us" style="color:#1a1a1a;text-decoration:none;">Contact</a>
        </p>
        <p style="margin:0;font-size:11px;color:#94a3b8;">Sent to {$user_email}. Keep your adoption code handy: {$sid}. You can check your application status anytime at <a href="{$status_url}" style="color:#94a3b8;">adopt.petcenterco.com/adoptions/status/</a>. If you did not submit this application, please contact us immediately.</p>
      </td></tr>
HTML;
}

/**
 * PENDING email — sent immediately on submission by adoption-application.php.
 */
function buildPendingEmail($application, $sid, $user_email, $display_name, $logo_url, $status_url, $adoptionsPetsBase) {
    $date_str = date('M j, Y \a\t g:i A');
    $pets = emailBuildPetList($application, $adoptionsPetsBase);
    $petsSection = emailRenderPetsSection($pets);
    $footerBlock = emailFooterBlock($user_email, $sid, $status_url);

    $subject = "Adoption Application Received — Pet Center";

    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,sans-serif;color:#1a1a1a;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">

      <tr><td align="center" style="padding-bottom:30px;">
        <img src="{$logo_url}" alt="Pet Center" style="width:140px;height:auto;">
      </td></tr>

      <tr><td align="center">
        <h1 style="margin:0;font-size:26px;font-weight:700;">Application Received</h1>
        <p style="margin:10px 0 0;font-size:16px;color:#4b5563;">Thank you, {$display_name} &mdash; we have your adoption application.</p>
      </td></tr>

      <tr><td align="center" style="padding:30px 0 40px;">
        <p style="margin:0;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Application Status</p>
        <p style="margin:5px 0 0;font-size:42px;font-weight:800;">Under Review</p>
      </td></tr>

      <tr><td>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb;border-radius:12px;padding:24px;border:1px solid #f1f5f9;">
          <tr><td style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Adoption Code</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:700;float:right;font-family:monospace;">{$sid}</span>
          </td></tr>
          <tr><td style="padding:12px 0;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Date Submitted</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:600;float:right;">{$date_str}</span>
          </td></tr>
          <tr><td style="padding:12px 0;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Applicant</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:600;float:right;">{$display_name}</span>
          </td></tr>
          <tr><td style="padding-top:12px;">
            <span style="font-size:14px;color:#6b7280;">Status</span>
            <span style="font-size:12px;color:#475569;font-weight:700;float:right;background:#f1f5f9;padding:3px 12px;border-radius:20px;border:1px solid #cbd5e1;text-transform:uppercase;">Under Review</span>
          </td></tr>
        </table>
      </td></tr>

      <tr><td style="padding:30px 0 0;">&nbsp;</td></tr>

      {$petsSection}

      <tr><td style="padding:0 0 30px;">
        <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;color:#1a1a1a;">What Happens Next</h3>

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;"><tr>
          <td style="width:32px;vertical-align:top;"><div style="width:24px;height:24px;background:#1a1a1a;border-radius:50%;color:#ffffff;text-align:center;line-height:24px;font-size:12px;font-weight:700;">1</div></td>
          <td style="padding-left:15px;vertical-align:top;">
            <p style="margin:0;font-size:15px;font-weight:700;">Application Review</p>
            <p style="margin:4px 0 0;font-size:14px;color:#4b5563;line-height:1.5;">Our adoption team carefully reviews every application &mdash; check back in 1&ndash;2 hours for an update. You'll receive a separate email the moment your status changes.</p>
          </td>
        </tr></table>

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;"><tr>
          <td style="width:32px;vertical-align:top;"><div style="width:24px;height:24px;background:#1a1a1a;border-radius:50%;color:#ffffff;text-align:center;line-height:24px;font-size:12px;font-weight:700;">2</div></td>
          <td style="padding-left:15px;vertical-align:top;">
            <p style="margin:0;font-size:15px;font-weight:700;">If Approved</p>
            <p style="margin:4px 0 0;font-size:14px;color:#4b5563;line-height:1.5;">We'll email you with flexible payment options (including BNPL and pay-on-delivery) and a secure checkout link to complete your adoption.</p>
          </td>
        </tr></table>

        <table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
          <td style="width:32px;vertical-align:top;"><div style="width:24px;height:24px;background:#1a1a1a;border-radius:50%;color:#ffffff;text-align:center;line-height:24px;font-size:12px;font-weight:700;">3</div></td>
          <td style="padding-left:15px;vertical-align:top;">
            <p style="margin:0;font-size:15px;font-weight:700;">Other Adopters May Apply Too</p>
            <p style="margin:4px 0 0;font-size:14px;color:#4b5563;line-height:1.5;">Applying doesn't guarantee ownership. Only being approved and completing checkout secures your new pet, so check your status regularly.</p>
          </td>
        </tr></table>
      </td></tr>

      <tr><td align="center" style="padding:10px 0 40px;">
        <a href="{$status_url}" style="display:inline-block;background:#ED7624;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:12px 28px;border-radius:8px;">Check Adoption Status</a>
      </td></tr>

      {$footerBlock}

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

    return ['subject' => $subject, 'html' => $html];
}

/**
 * APPROVED email — sent from update-adoption.php the moment an admin sets
 * status=APPROVED. Mirrors the exact payment/BNPL messaging shown on the
 * live /adoptions/status/ dashboard's APPROVED branch, word for word.
 */
function buildApprovedEmail($application, $sid, $user_email, $display_name, $logo_url, $status_url, $adoptionsPetsBase) {
    $pets = emailBuildPetList($application, $adoptionsPetsBase);
    $petsSection = emailRenderPetsSection($pets);
    $checkout_url = $application['checkout_url'] ?? '';
    $footerBlock = emailFooterBlock($user_email, $sid, $status_url);

    $subject = "Great News — Your Adoption Application Was Approved! — Pet Center";

    $checkoutButton = $checkout_url
        ? "<tr><td align=\"center\" style=\"padding:10px 0 30px;\"><a href=\"" . htmlspecialchars($checkout_url) . "\" style=\"display:inline-block;background:#0d7c4a;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:8px;\">Proceed to Checkout</a></td></tr>"
        : "<tr><td align=\"center\" style=\"padding:10px 0 30px;\"><a href=\"{$status_url}\" style=\"display:inline-block;background:#0d7c4a;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:8px;\">View Checkout Link on Status Page</a></td></tr>";

    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,sans-serif;color:#1a1a1a;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">

      <tr><td align="center" style="padding-bottom:30px;">
        <img src="{$logo_url}" alt="Pet Center" style="width:140px;height:auto;">
      </td></tr>

      <tr><td align="center" style="padding-bottom:8px;">
        <div style="width:64px;height:64px;border-radius:50%;background:#e6f7ef;margin:0 auto 16px;text-align:center;line-height:64px;">
          <span style="font-size:30px;color:#0d7c4a;">&#10003;</span>
        </div>
        <h1 style="margin:0;font-size:26px;font-weight:700;color:#0d7c4a;">Congratulations, {$display_name}!</h1>
        <p style="margin:10px 0 0;font-size:16px;color:#4b5563;">Your adoption application has been approved.</p>
      </td></tr>

      <tr><td align="center" style="padding:24px 0 30px;">
        <span style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#0d7c4a;background:rgba(13,124,74,0.12);padding:6px 18px;border-radius:20px;">Approved</span>
      </td></tr>

      <tr><td>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb;border-radius:12px;padding:24px;border:1px solid #f1f5f9;">
          <tr><td style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Adoption Code</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:700;float:right;font-family:monospace;">{$sid}</span>
          </td></tr>
          <tr><td style="padding-top:12px;">
            <span style="font-size:14px;color:#6b7280;">Applicant</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:600;float:right;">{$display_name}</span>
          </td></tr>
        </table>
      </td></tr>

      <tr><td style="padding:30px 0 0;">&nbsp;</td></tr>

      {$petsSection}

      <tr><td style="padding:0 0 20px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb;border-radius:12px;padding:22px;">
          <tr><td>
            <p style="margin:0 0 14px;font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#1a1a1a;">You are eligible for the following flexible payment options during checkout</p>

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:12px;"><tr>
              <td style="width:14px;vertical-align:top;padding-top:5px;"><div style="width:7px;height:7px;background:#ED7624;border-radius:50%;"></div></td>
              <td style="padding-left:8px;font-size:13.5px;color:#3d2817;line-height:1.65;"><strong style="color:#1a1a1a;">BNPL (Buy Now Pay Later):</strong> Select &ldquo;Apply for BNPL&rdquo; during checkout inside the card checkout option. It only takes about 2&ndash;5 minutes to apply and is reviewed within 24 hours. Once approved, your pet is usually shipped or delivered locally the same day.</td>
            </tr></table>

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:12px;"><tr>
              <td style="width:14px;vertical-align:top;padding-top:5px;"><div style="width:7px;height:7px;background:#ED7624;border-radius:50%;"></div></td>
              <td style="padding-left:8px;font-size:13.5px;color:#3d2817;line-height:1.65;"><strong style="color:#1a1a1a;">Pay on Delivery:</strong> Adopt your pet and pay only when you receive them at home.</td>
            </tr></table>

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:14px;"><tr>
              <td style="width:14px;vertical-align:top;padding-top:5px;"><div style="width:7px;height:7px;background:#ED7624;border-radius:50%;"></div></td>
              <td style="padding-left:8px;font-size:13.5px;color:#3d2817;line-height:1.65;"><strong style="color:#1a1a1a;">Installment Plans (Pay in 4 / In-House):</strong> Pay for your pet in 4 payments over the months via your PayPal account, or choose our in-house installment option for even more flexible terms. To set up either plan, contact our customer support team &mdash; they will help configure the plan for you.</td>
            </tr></table>

            <p style="margin:0 0 14px;font-size:13.5px;color:#3d2817;line-height:1.6;">Prefer to pay in full now? At checkout just choose your preferred secured payment method available and checkout directly, hassle free.</p>

            <div style="background:#fff;border-radius:8px;padding:14px 16px;margin-bottom:12px;font-size:12.5px;color:#6b7280;line-height:1.6;">
              Notice: Shipping/delivery cost might vary for some pets or not be included at all for some pets. If not included during checkout, you will see just the allocated adoption fee to pay for. If included, a shipping and delivery cost will automatically be added to your total fee.<br><br>
              BNPL and other services also cover shipping and delivery cost &mdash; if selected, you will also be eligible to pay the shipping and delivery cost only when your pet is home. However, your BNPL application must have been approved and your selected preferred payment method choice to use and pay on delivery must have been verified too.
            </div>

            <p style="margin:0;font-size:12.5px;color:#6b7280;font-style:italic;">Tip: Always check your inbox/spam folder for email updates from us to stay up to date.</p>
          </td></tr>
        </table>
      </td></tr>

      {$checkoutButton}

      {$footerBlock}

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

    return ['subject' => $subject, 'html' => $html];
}

/**
 * REJECTED email — sent from update-adoption.php the moment an admin sets
 * status=REJECTED (including a later reversal from APPROVED). Includes the
 * admin's rejection_notes verbatim, matching the status dashboard's tone.
 */
function buildRejectedEmail($application, $sid, $user_email, $display_name, $logo_url, $status_url, $adoptionsPetsBase) {
    $pets = emailBuildPetList($application, $adoptionsPetsBase);
    $petsSection = emailRenderPetsSection($pets);
    $reason = trim($application['rejection_notes'] ?? '');
    $reasonHtml = $reason
        ? nl2br(htmlspecialchars($reason))
        : 'No additional reason was provided. If you have questions about this decision, please reach out to our support team.';
    $footerBlock = emailFooterBlock($user_email, $sid, $status_url);

    $subject = "Update on Your Adoption Application — Pet Center";

    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,sans-serif;color:#1a1a1a;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">

      <tr><td align="center" style="padding-bottom:30px;">
        <img src="{$logo_url}" alt="Pet Center" style="width:140px;height:auto;">
      </td></tr>

      <tr><td align="center">
        <h1 style="margin:0;font-size:26px;font-weight:700;">Application Update</h1>
        <p style="margin:10px 0 0;font-size:16px;color:#4b5563;">Hi {$display_name}, thank you for your interest in adopting with us.</p>
      </td></tr>

      <tr><td align="center" style="padding:24px 0 30px;">
        <span style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#c0392b;background:rgba(192,57,43,0.1);padding:6px 18px;border-radius:20px;">Not Approved</span>
      </td></tr>

      <tr><td>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb;border-radius:12px;padding:24px;border:1px solid #f1f5f9;">
          <tr><td style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <span style="font-size:14px;color:#6b7280;">Adoption Code</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:700;float:right;font-family:monospace;">{$sid}</span>
          </td></tr>
          <tr><td style="padding-top:12px;">
            <span style="font-size:14px;color:#6b7280;">Applicant</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:600;float:right;">{$display_name}</span>
          </td></tr>
        </table>
      </td></tr>

      <tr><td style="padding:30px 0 0;">&nbsp;</td></tr>

      {$petsSection}

      <tr><td style="padding:0 0 30px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fdecea;border-radius:12px;padding:22px;">
          <tr><td>
            <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#c0392b;">Reason</p>
            <p style="margin:0;font-size:14px;color:#7a2a20;line-height:1.65;">{$reasonHtml}</p>
          </td></tr>
        </table>
      </td></tr>

      <tr><td style="padding:0 0 30px;">
        <p style="margin:0;font-size:14px;color:#4b5563;line-height:1.65;">This decision doesn't reflect on you as a pet parent &mdash; every home and every pet's needs are different. We'd love for you to browse our other available pets, as a different match might be a perfect fit.</p>
      </td></tr>

      <tr><td align="center" style="padding:10px 0 40px;">
        <a href="https://adopt.petcenterco.com/" style="display:inline-block;background:#ED7624;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:12px 28px;border-radius:8px;margin-right:10px;">Browse Other Pets</a>
      </td></tr>

      <tr><td align="center" style="padding:0 0 20px;font-size:13.5px;color:#6b7280;">
        Click our floating contact button on the site if you have any questions about this decision.
      </td></tr>

      {$footerBlock}

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

    return ['subject' => $subject, 'html' => $html];
}

/**
 * Sends one of the three templates via the existing raw mail() pattern
 * already used by adoption-application.php — deliberately NOT switched to
 * the PHPMailer/sendPdfEmail() helper, per explicit instruction to leave
 * the working mail delivery method untouched.
 */
function sendAdoptionEmail($user_email, $subject, $html) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Pet Center <noreply@petcenterco.com>\r\n";
    $headers .= "Reply-To: adopt@petcenterco.com\r\n";
    return @mail($user_email, $subject, $html, $headers);
}
