<?php
require_once 'config/config.php';

try {
    $pdo = (new config())->getConnexion();
    $email = 'eyamissaoui@gmail.com'; // The email from the screenshot

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "User found:\n";
        echo "ID: " . $user['id_utilisateur'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Stored Password: " . $user['mot_de_passe'] . "\n";
        echo "Stored Password Length: " . strlen($user['mot_de_passe']) . "\n";

        $inputPassword = '1234';
        echo "Testing with input password: '$inputPassword'\n";

        if (password_verify($inputPassword, $user['mot_de_passe'])) {
            echo "password_verify: TRUE\n";
        } else {
            echo "password_verify: FALSE\n";
        }

        if ($inputPassword === $user['mot_de_passe']) {
            echo "Strict comparison (===): TRUE\n";
        } else {
            echo "Strict comparison (===): FALSE\n";
        }
    } else {
        echo "User NOT found with email: $email\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>