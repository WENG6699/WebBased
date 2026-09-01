<?php require '../_base.php'; ?>
<?php auth('Super Admin'); ?>
<?php

$_err = [];

if (is_post()) {
    $username = post('username');
    $email = post('email');
    $phone = post('phone');

    if (!$username) {
        $_err['username'] = 'Username is required';
    } elseif (!is_unique('member', 'username', $username, $_user->member_id, 'member_id')) {
        $_err['username'] = 'Username is already taken';
    }

    if (!$email) {
        $_err['email'] = 'Email is required';
    } elseif (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    } elseif (!is_unique('member', 'email', $email, $_user->member_id, 'member_id')) {
        $_err['email'] = 'Email is already registered';
    }

    if (!$phone) {
        $_err['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^(\+?60|0)[0-9]{8,10}$/', str_replace([' ', '-'], '', $phone))) {
        $_err['phone'] = 'Must be a valid Malaysian phone number, e.g. 012-3456789';
    }

    if (!$_err) {
        $pdo->prepare("UPDATE member SET username = ?, email = ?, phone = ?, updated_at = NOW() WHERE member_id = ?")
            ->execute([$username, $email, $phone, $_user->member_id]);

        // Keep session user object in sync so the header/profile page
        // reflects the change immediately without needing to re-login.
        $_user->username = $username;
        $_user->email = $email;
        $_user->phone = $phone;

        temp('info', 'Profile updated successfully.');
        redirect('/superadmin/profile.php');
    }
}

$username = $username ?? $_user->username;
$email = $email ?? $_user->email;
$phone = $phone ?? $_user->phone;

$_title = 'Edit Profile';
require '../_head.php';
?>

<h1>Edit Profile</h1>

<div class="user-chip" style="margin-bottom:24px;">
    <?= user_avatar($_user, 48) ?>
    <div>
        <strong style="display:block;"><?= h($_user->username) ?></strong>
        <span style="color:var(--muted); font-size:13px;"><?= h($_user->role) ?></span>
    </div>
</div>

<form method="post" novalidate>
    <label for="username">Username</label>
    <?= html_text('username') ?>
    <?= err('username') ?>

    <label for="email">Email</label>
    <?= html_text('email') ?>
    <?= err('email') ?>

    <label for="phone">Phone Number</label>
    <?= html_text('phone') ?>
    <?= err('phone') ?>

    <button>Save Changes</button>
    <a href="/superadmin/profile.php">Cancel</a>
</form>

<?php require '../_foot.php'; ?>