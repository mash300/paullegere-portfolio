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
    <title>Contact Form</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
  <link rel="shortcut icon" type="image/png" href="/images/favicon.png">
    <link rel="stylesheet" href="/dist/style.css">
    <link rel="stylesheet" href="portfolioStyles.css">
    <!-- Add your CSS stylesheets here -->
</head>
<body>
<header class="header">
  <div class="overlay has-fade"></div>

  <nav class="container container--pall flex flex-jc-sb flex-ai-c">
    <a href="/" class="header__logo">
      <!-- <img src="/images/logo_temp.png" alt="PL Design"> -->
      <img src="/images/PLD_Icon.svg" alt="PL Design">
    </a>
    <a id="btnBurger" href="#" class="header__toggle  hide-for-desktop">
      <span></span>
      <span></span>
      <span></span>
    </a>

    <div class="header__links hide-for-mobile">
      <a href="index.html">Home</a>
      <a href="/about.html">About</a>
      <a href="/index.html#case_studies">Case Studies</a>
      <a href="      
">Contact</a>
    </div>

    <!-- <a href="#" class="button header__contact hide-for-mobile">Contact</a> -->
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

<section class="feature">
  <div class="feature__content container container--pall">
    
  <!-- Original Contact Page heading -->
    <!-- <div class="feature__intro">
      <h2 class="contact-heading">
        Contact
      </h2>
    </div> -->
    
    <!-- ================== PHP START ===================== -->

    <?php if (!empty($successMessage)): ?>
        
        <div class="feature__intro" style="min-height: 100svh;">
            <h2 class="contact-heading">
                Message Sent!
            </h2>
            <!-- <h2>Success</h2> -->
            <p><?php echo $successMessage; ?></p>
        </div>
    </div>
</section>



        <div class="footerSection">
  <footer class="footer container container--pall flex flex-jc-sb flex-ai-c">
    <div class="footer__logo">
      <a href="index.html">
        <img src="/images/PLD_Icon.svg" alt="logo">
      </a>
    </div>
  

  
    <div class="footer__links col1">
      <a href="index.html">Home</a>
      <a href="/about.html">About</a>
    </div>
    
    <div class="footer__links col2">
      <a href="/index.html#case_studies">Case Studies</a>

    </div>
  
    <div class="footer__contact col3">
      <a href="/contact.php" class="button2">Contact</a>
      <!-- <a href="/contact.php" class="button2">Contact</a> -->
      <div class="footer_copyright">
        <!-- &copy; PL Design. All Rights Reserved. -->
        <a href="https://www.linkedin.com/in/paul-legere/" target="_blank">
          <img src="/images/linkedin_logo_inline.svg" alt="Paul legere on LinkedIn">
        </a>
      </div>
    </div>

  </footer> 
</div>

    <script src="/app/js/script.js"></script>
    <?php else: ?>
        <!-- <h2>Contact</h2> -->
        <section class="feature">
        <div>

        <div class="feature__intro">
            <h2 class="contact-heading">
                Get in Touch
            </h2>
            <!-- <h3>Get in Touch</h3> -->
            <p class="contact-v2__lead">
              Have a role, project, or collaboration in mind? I’d love to hear from you.
            </p>
            <p class="contact-v2__meta">
              Based in Nova Scotia, open to remote opportunities across Canada and beyond.
            </p>
        </div>
        
        <!-- Form verification error messages to user - form validation feedback -->
        <?php if (!empty($errors)): ?>
          <div class="contact-errors">
              <strong>Please correct the following:</strong>
              <ul>
                  <?php foreach ($errors as $error): ?>
                      <li><?php echo htmlspecialchars($error); ?></li>
                  <?php endforeach; ?>
              </ul>
          </div>
        <?php endif; ?>


        <div class="contact-hero">
            <div class="form-wrapper">
                <div class="my-contact-form">
                    <form class="fcf-form-class" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"><br><br>
                        
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"><br><br>
                        
                        <label for="message">Message:</label>
                        <textarea id="message" name="message" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea><br><br>
                        
                        <div class="g-recaptcha" data-sitekey="6LdAtRUtAAAAAPlG9SKKn440p3ZQ9zPR6KKC8uaF"></div><br>
                        
                        <!-- <button type="submit" value="Submit"> -->
                        <div class="fcf-form-group">
                        <button type="submit" id="fcf-button" value="Submit" class="fcf-btn fcf-btn-primary fcf-btn-lg fcf-btn-block">Send</button>
                    </div>
                    </form>
                </div>
            </div>
            <div class="kayto-painting"></div>
            <div class="contact-block">
            </div>
        </div>
        </div>
        </div>
</section>
<!-- <a href="#" class="attribution">Copyright Paul Legere 2021</a> -->
<div class="footerSection">
<footer class="footer container container--pall flex flex-jc-sb flex-ai-c">
  <div class="footer__logo">
      <a href="#">
        <img src="/images/PLD_Icon.svg" alt="logo">
      </a>
    </div>

    <div class="footer__links col1">
      <a href="index.html">Home</a>
      <a href="/about.html">About</a>
    </div>
    
    <div class="footer__links col2">
      <a href="/index.html#case_studies">Case Studies</a>
    </div>

    <div class="footer__contact col3">
      <a href="/contact.php" class="button2">Contact</a>
      <!-- <a href="/contact.php" class="button2">Contact</a> -->
      <div class="footer_copyright">
        <!-- &copy; PL Design. All Rights Reserved. -->
        <a href="https://www.linkedin.com/in/paul-legere/" target="_blank">
          <img src="/images/linkedin_logo_inline.svg" alt="Paul legere on LinkedIn">
        </a>
      </div>
    </div>
  
    <!-- <div class="footer__contact">
      <a href="/contact.php" class="button2">Contact</a>
    </div> -->
  
    <!-- <div class="footer_copyright">
      &copy; PL Design. All Rights Reserved.
    </div> -->
  </footer> 
</div>
    <script src="/app/js/script.js"></script>

    <?php endif; ?>
    <!-- Add your JavaScript scripts here -->

</body>
</html>
