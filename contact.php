<?php

$config = require __DIR__ . '/../private_config/config.php';
// echo '<p style="color: red;">Config loaded successfully.</p>';
$secretKey = $config['recaptcha_secret'];
$email_to = $config['contact_email'];
$email_subject = "New form submission";

$errors = [];
$successMessage = '';

$name = '';
$email = '';
$message = '';

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function has_header_injection($str) {
    return preg_match("/[\r\n]/", $str);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? clean_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $message = isset($_POST['message']) ? clean_input($_POST['message']) : '';
    $responseKey = $_POST['g-recaptcha-response'] ?? '';
    $userIP = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || has_header_injection($email)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($message)) {
        $errors[] = "Message is required.";
    }

    if (empty($responseKey)) {
        $errors[] = "Please complete the captcha.";
    }

    if (empty($errors)) {
        $verifyUrl = "https://www.google.com/recaptcha/api/siteverify";

        $postData = http_build_query([
            'secret' => $secretKey,
            'response' => $responseKey,
            'remoteip' => $userIP
        ]);

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData,
                'timeout' => 10
            ]
        ];

        $context = stream_context_create($options);

        // $response = file_get_contents($verifyUrl, false, $context);
        // $response = json_decode($response);

        // Updating Google verification request method - part 1
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $verifyUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $errors[] = "Captcha verification failed. Please try again.";
            curl_close($ch);
        } else {
            curl_close($ch);
            $response = json_decode($response);
        }

        // Updating Google verification request method - part 2
        // if (!empty($response->success)) {

        if (isset($response) && !empty($response->success)) { 
            $email_message = "Name: {$name}\n";
            $email_message .= "Email: {$email}\n";
            $email_message .= "Message:\n{$message}\n";

            $headers = "From: Paul Legere Website <info@paullegere.net>\r\n";
            $headers .= "Reply-To: {$email}\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if (mail($email_to, $email_subject, $email_message, $headers)) {
                $successMessage = "Thanks for getting in touch. I'll get back to you soon.";
            } else {
                $errors[] = "Sorry, the message could not be sent. Please try again later.";
            }
        } else {
            $errors[] = "Invalid captcha, please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact | Paul Legere — Product Designer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
  <link rel="shortcut icon" type="image/png" href="/images/favicon.png">
  <link rel="stylesheet" href="/dist/style.css">
  <link rel="stylesheet" href="/portfolioStyles.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<header class="header">
<div class="overlay has-fade"></div>
<nav class="container container--pall flex flex-jc-sb flex-ai-c">
<a class="header__logo" href="/">
<!-- <img src="/images/logo_temp.png" alt="PL Design"> -->
<img alt="PL Design" src="/images/PLD_Icon.svg"/>
</a>
<a class="header__toggle hide-for-desktop" href="#" id="btnBurger">
<span></span>
<span></span>
<span></span>
</a>
<div class="header__links hide-for-mobile">
<a href="index.html">Home</a>
<a href="/about.html">About</a>
<a href="/index.html#case_studies">Case Studies</a>
<a href="/contact.php" aria-current="page">Contact</a>
</div>
</nav>
<div class="header__menu has-fade">
<a href="index.html">Home</a>
<!-- <a href="index.html">Process</a> -->
<a href="/about.html">About</a>
<a href="/index.html#case_studies">Case Studies</a>
<a href="/contact.php">Contact</a>
<a href="#" style="visibility: hidden;">Link</a>
</div>
</header>
<main class="contact-page">
  <section class="contact-page__content container container--pall" aria-labelledby="contact-title">
    <?php if (!empty($successMessage)): ?>
      <div class="contact-page__success" role="status">
        <h1 class="display contact-page__title" id="contact-title">Message Sent!</h1>
        <p class="contact-page__lead"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
    <?php else: ?>
    <div class="contact-page__intro">
      <h1 class="display contact-page__title" id="contact-title">Get in Touch</h1>
      <p class="contact-page__lead">Have a role or project in mind? I’d love to hear from you.</p>
    </div>
    <?php if (!empty($errors)): ?>
      <div class="contact-errors" role="alert">
        <strong>Please correct the following:</strong>
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <div class="contact-hero">
      <div class="form-wrapper">
        <div class="my-contact-form">
          <form class="fcf-form-class" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <div class="contact-page__field">
              <label for="name">Name</label>
              <input type="text" id="name" name="name" autocomplete="name" required value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="contact-page__field">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" autocomplete="email" required value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="contact-page__field">
              <label for="message">Message</label>
              <textarea id="message" name="message" required><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="g-recaptcha" data-sitekey="6LdAtRUtAAAAAPlG9SKKn440p3ZQ9zPR6KKC8uaF"></div>
            <div class="fcf-form-group">
              <button type="submit" id="fcf-button" value="Submit" class="fcf-btn fcf-btn-primary fcf-btn-lg fcf-btn-block">Send</button>
            </div>
          </form>
        </div>
      </div>
      <div class="kayto-painting" role="img" aria-label="Illustration of Kato the cat"></div>
    </div>
    <?php endif; ?>
  </section>
</main>
<div class="footerSection">
  <footer class="footer container container--pall">

    <div class="footer__logo">
      <a href="/">
        <img src="/images/PLD_Icon.svg" alt="PL Design">
      </a>
    </div>

    <nav class="footer__links" aria-label="Footer navigation">
      <a href="/index.html">Home</a>
      <a href="/about.html">About</a>
      <a href="/index.html#case_studies">Case Studies</a>
      <a href="/contact.php">Contact</a>
    </nav>

    <div class="footer__contact">
      <a href="https://www.linkedin.com/in/paul-legere/"
         target="_blank"
         rel="noopener noreferrer"
         aria-label="Paul Legere on LinkedIn">
        <img src="/images/linkedin_logo_inline.svg" alt="">
      </a>
    </div>

  </footer>
</div>
<script src="/app/js/script.js"></script>
</body>
</html>
