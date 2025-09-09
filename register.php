<?php 
include "config/config.php";

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $agree = isset($_POST['agree']);
    
    if (empty($username)) {
        $errors[] = 'Логин не может быть пустым';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Логин должен содержать минимум 3 символа';
    } elseif (strlen($username) > 16) {
        $errors[] = 'Логин не может быть длиннее 16 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль не может быть пустым';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if (empty($email)) {
        $errors[] = 'Email не может быть пустым';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный формат email';
    }
    
    if (!$agree) {
        $errors[] = 'Необходимо принять пользовательское соглашение';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $stmt = $pdo->prepare("SELECT email, username FROM users WHERE email = ? OR username = ?");
                $stmt->execute([$email, $username]);
                $existing = $stmt->fetch();
                
                if ($existing['email'] === $email) {
                    $errors[] = 'Пользователь с таким email уже существует';
                }
                if ($existing['username'] === $username) {
                    $errors[] = 'Пользователь с таким логином уже существует';
                }
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, ram) VALUES (?, ?, ?, 'Пользователь', 4)");
                $stmt->execute([$username, $email, $hashedPassword]);
                
                session_start();
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'Пользователь';
                
                $success = 'Регистрация успешна! Перенаправление...';
            }
        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных. Попробуйте позже.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="keywords" content="nursultan, nursultan nextgen, nurik, wexside, celestial, akrien, minecraft, minecraft 1.8, minecraft 1.12.2, minecraft 1.16.5, deadcode, нурсултан, nursultan client, нурсултан клиент, нурик, вексайд, целестиал, акриен, майнкрафт, чит, читы, читы для майнкрафт, читы для minecraft, чит на майнкрафт, чит на minecraft">
    <meta name="description" content=" Nursultan client - Лучший клиент для комфортной игры. Для майнкрафт 1.16.5 ">
    <title id="title"><?=$nameClient ?> - Лучший клиент для комфортной игры.</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="canonical" href="https://nursultan.fun/?lang=ru">
    <link rel="canonical" href="https://nursultan.fun/?lang=en">
    <link rel="canonical" href="https://nursultan.fun/?lang=tr">
    <link rel="canonical" href="https://nursultan.fun/?lang=pl">
    <link rel="canonical" href="https://nursultan.fun/?lang=ua">
    <link href="assets/register/main.9ebd742b.css" rel="stylesheet">
        <link href="config/config.css" rel="stylesheet">
    <script id="cf-turnstile-script" src="assets/register/api.js.Без названия" defer="" async=""></script>
    <style>
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 9999;
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        .toast i {
            margin-right: 10px;
        }
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .form-control.error {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <noscript>Вам нужно включить JavaScript, чтобы пользоваться сайтом.</noscript>
    <div id="root">
        <nav class="NavBar_navBarScroll__Oh0-7 navbar navbar-expand-md fixed-top">
            <div class="container-xl">
                <a class="navbar-brand mr-2" href="index.php"><?=$nameClient ?></a>
                <button aria-controls="navbar" type="button" aria-label="Toggle navigation" class="navbar-toggler collapsed">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse collapse" id="navbar">
                    <div class="navbar-nav">
                        <div class="vr d-none d-md-flex pt-3 pb-3 mr-2 ml-0 mt-auto mb-auto"></div>
                        <div class="mr-3 dropdown">
                            <button type="button" id="react-aria8595193081-:r0:" aria-expanded="false" class="NavBar_languageSelector__0l8kx dropdown-toggle btn">
                                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAqElEQVR4Xu2aoQ1CYRjEPsskbMASOPZhDzwrPIlhCYIgwaPeBj/+boS2SU3l6ZsRERERESj3x2cjO/u+L7IOkIGmA2Sg6QAZaDpABpoOkIGmA2SgOa/vb5GduWwLbQWaFWhWoFmBZgWaFWhWoFmBZgWaFWhWoFmBZgWaz8NpkZ33HBdZB8hA0wEy0HSADDQdIANNB8hA0wEy0Jzb+bqRzeuwiIiIiFD4AyiolWWRAEPWAAAAAElFTkSuQmCC" alt="">
                                   Русский
                </button>
              </div>
              <hr class="mt-1 mb-1 d-md-none">
            </div>
                    <div class="ml-auto navbar-nav">
                        <a class="nav-link" href="<?=$discordLink?>" target="_blank" rel="noreferrer">
                            <i class="fa-solid fa-headset mr-2"></i>Поддержка
                        </a>
              <a class="nav-link mr-2" href="product.php">
                <i class="fa-solid fa-bag-shopping mr-2"></i>Купить
              </a>
              <hr class="mt-1 mb-3 d-md-none">
              <a class="btn btn-lg btn-gradient" href="login.php">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Авторизация
              </a>
            </div>
          </div>
        </div>

      </nav>
        <div class="Auth_content__MWQAE">
            <div class="toast-container"></div>
            <div class="Auth_panel__9K-0+">
                <h4>Регистрация</h4>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="input-group input-group-lg mb-2 mt-4">
                        <input type="text" name="username" class="form-control <?php echo (in_array('Логин не может быть пустым', $errors) || in_array('Логин должен содержать минимум 3 символа', $errors) || in_array('Логин не может быть длиннее 16 символов', $errors) || in_array('Пользователь с таким логином уже существует', $errors)) ? 'error' : ''; ?>" placeholder="Придумайте логин" maxlength="16" value="<?php echo $success ? '' : htmlspecialchars(''); ?>">
                    </div>
                    <div class="input-group input-group-lg mb-2">
                        <input type="password" name="password" class="form-control <?php echo (in_array('Пароль не может быть пустым', $errors) || in_array('Пароль должен содержать минимум 6 символов', $errors)) ? 'error' : ''; ?>" placeholder="Придумайте пароль" maxlength="100" value="">
                    </div>
                    <div class="input-group input-group-lg mb-2">
                        <input type="password" name="confirm_password" class="form-control <?php echo in_array('Пароли не совпадают', $errors) ? 'error' : ''; ?>" placeholder="Повторите пароль" maxlength="100" value="">
                    </div>
                    <div class="input-group input-group-lg mb-4">
                        <input type="email" name="email" class="form-control <?php echo (in_array('Email не может быть пустым', $errors) || in_array('Неверный формат email', $errors) || in_array('Пользователь с таким email уже существует', $errors)) ? 'error' : ''; ?>" placeholder="Введите E-mail" maxlength="50" value="<?php echo $success ? '' : htmlspecialchars($email ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="checkbox-container">
                            Я полностью ознакомлен и <a href="footer/elua.php" target="_blank">принимаю пользовательское соглашение</a>, а также даю свое согласие на <a href="footer/personal.php" target="_blank">обработку персональных данных</a>.
                            <input type="checkbox" name="agree" value="1" <?php echo ($success || !isset($_POST['agree'])) ? '' : 'checked'; ?>>
                            <span class="checkmark"></span>
                        </label>
                    </div>
                    <div id="cf-turnstile" style="width: 300px; height: 65px;">
                        <div>
                            <template shadowrootmode="closed">
                                <iframe src="https://challenges.cloudflare.com/cdn-cgi/challenge-platform/h/b/turnstile/if/ov2/av0/rcv/2x5c7/0x4AAAAAAAB5MBip7H_Q8dlq/dark/fbE/new/normal/auto/" allow="cross-origin-isolated; fullscreen; autoplay" sandbox="allow-same-origin allow-scripts allow-popups" id="cf-chl-widget-2x5c7" tabindex="0" title="Виджет с действием &quot;challenge&quot; Cloudflare" style="border: none; overflow: hidden; width: 300px; height: 65px;"></iframe>
                            </template>
                            <input type="hidden" name="cf-turnstile-response" id="cf-chl-widget-2x5c7_response" value="0.l5lefxOi9gezR_qChQurCgCi4aMPk_lcjrfr0qVJ09xF0SKizv7niJjFv0_aD5H9BW2ZU3eB3Qz_j_wdUxixsNn7KO8Kb50WjhC1VHyfu89b1Gpf1tRuUo6sVURW0cj1dopgiMsfgRbz6VRQf_1pChNcknGmFZRdgbwcsfwz5zhqydrjyTh7AOUtBGqjXk97wocBMuFfHSlu_1OkyjRWXECLJV_UVTgNMnTYTKsrFO2oDJUONN6n-DEtFvGBP5gss6h9tWhfclcP9dscsiDWVM-fh5t9FW2cz5OZ6pHIGZ-e0cA6G01mh-M7eF9DYVIFTs28sSaozjEaf03koDkeh_8An5W4hmIw69Oip6R17JJVLXm54zGVehsFksF93qSXeY1_f6ohPHpKs-kZQ-KUn0Hwx78xvo-SDNCAmtrY7TRovclRZJnQUJnWiuJBXKsJZ-DORv5f5VhVD-irQW6Q8OWz334vqr8GA_DUondRbfJqjFclXyJXTrap54dCBNOQ2a2PxWVaJbdDlmLzkoFjDYoZUqpqfitQtqsT8pTc1sMIEyJG1KpgY-oFdCQVZmQxQKgv_OsYIaCrrkIb_BBO7D-wwql1rgqgDHaEZWXl9PC8sUNBHnE9rniwXKfGYioCTJFvkUzW-uJ-nvAF9Uqxwn8zTjN10Nn7dCMb98o-4tTIWfA39lntfEsNA0J6j4NHY_gd5itXzuYJ5YMO9hz1d4gp1SI40zjcvQlKsDBu7-u9sXkyar9_wgcHnlDvFda0OTp-JSRWVqftFYLh_3rFtOIJItKy6Fp2t22vn4bfEvAXHrcBfIX35ZSFkxNRzPgvj95TG7Q7yW32i3oPSYYI7Xt520Qyyrmhrc-pxoZxzngkTXjxkzyYo72loi7VZaxDtNVcX33cTW13inhjbyQxeg.57G9LFSZBWbU-BHuIb7Nkg.c83999a7476d0ba48b6677c1c71f4e1e4fa113b43b3658d138e583ff9840bd7c">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-lg btn-gradient w-100 mt-3">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>Зарегистрироваться
                    </button>
                </form>
                <div class="button-group d-flex flex-wrap align-items-center justify-content-center w-100 mt-4">
                    <a class="Auth_authLink__tBmtP" href="login.php">Уже есть аккаунт?</a>
                </div>
            </div>
        </div>
        <div class="Footer_footer__gtV2S">
        <div class="container-xl">
          <div class="Footer_logo__C5wy8"><?=$nameClient ?></div>
          <div class="Footer_links__7+5fr">
            <div class="Footer_leftLinks__9B3S6">
              <span class="mb-2">Навигация</span>
              <a class="Footer_link__QicBf" href="">Главная</a>
              <a class="Footer_link__QicBf" href="product.php">Купить</a>
              <a class="Footer_link__QicBf" href="<?=$dicsordLink ?>" target="_blank" rel="noreferrer">Поддержка</a>
            </div>
            <div class="Footer_centerLinks__r0miB">
              <span></span>
              <span>Почта для связи: ntfhelp@mail.ru (ПН-ПТ 10:00-18:00 МСК)</span>
            </div>
            <div class="Footer_rightLinks__pqAM1">
              <span class="mb-2">Навигация</span>
              <a class="Footer_link__QicBf" href="footer/personal.php">Обработка персональных данных</a>
              <a class="Footer_link__QicBf" href="footer/personal.php">Пользовательское соглашение</a>
              <a class="Footer_link__QicBf" href="footer/rules.php">Правила пользования</a>
            </div>
          </div>
          <div class="Footer_copyright__7kGoh">
            <span>Copyright © <?=$nameClient ?>Client 2025</span>
          </div>
        </div>
      </div>
    </div>
    
    <?php if ($success): ?>
        <div class="toast" id="successToast">
            <i class="fa-solid fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <script>
        <?php if ($success): ?>
            setTimeout(function() {
                document.getElementById('successToast').classList.add('show');
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 2000);
            }, 100);
        <?php endif; ?>
        
        (function(){
            function c(){
                var b=a.contentDocument||a.contentWindow.document;
                if(b){
                    var d=b.createElement('script');
                    d.innerHTML="window.__CF$cv$params={r:'971b4d95ef915779',t:'MTc1NTYyMzgzMi4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";
                    b.getElementsByTagName('head')[0].appendChild(d)
                }
            }
            if(document.body){
                var a=document.createElement('iframe');
                a.height=1;
                a.width=1;
                a.style.position='absolute';
                a.style.top=0;
                a.style.left=0;
                a.style.border='none';
                a.style.visibility='hidden';
                document.body.appendChild(a);
                if('loading'!==document.readyState)c();
                else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);
                else{
                    var e=document.onreadystatechange||function(){};
                    document.onreadystatechange=function(b){
                        e(b);
                        'loading'!==document.readyState&&(document.onreadystatechange=e,c())
                    }
                }
            }
        })();
    </script>
    <iframe height="1" width="1" style="position: absolute; top: 0px; left: 0px; border: none; visibility: hidden;" src="assets/register/saved_resource.html"></iframe>
</body>
</html>