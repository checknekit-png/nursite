<?php 
include "config/config.php";

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($login)) {
        $errors[] = 'Логин или email не может быть пустым';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль не может быть пустым';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_banned']) {
                    $errors[] = 'Ваш аккаунт заблокирован. Причина: ' . ($user['ban_reason'] ?? 'Не указана');
                } else {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    if ($remember) {
                        setcookie('remember_token', hash('sha256', $user['id'] . $user['password']), time() + (30 * 24 * 60 * 60), '/');
                    }
                    
                    $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, activity_type) VALUES (?, ?)");
                    $stmt->execute([$user['id'], 'Вход в систему']);
                    
                    $success = 'Авторизация успешна! Перенаправление...';
                }
            } else {
                $errors[] = 'Неверный логин или пароль';
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
    <link href="assets/login/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/login/all.min.css" crossorigin="use-credentials">
    <link rel="canonical" href="https://nursultan.fun/?lang=ru">
    <link rel="canonical" href="https://nursultan.fun/?lang=en">
    <link rel="canonical" href="https://nursultan.fun/?lang=tr">
    <link rel="canonical" href="https://nursultan.fun/?lang=pl">
    <link rel="canonical" href="https://nursultan.fun/?lang=ua">
    <link href="assets/login/main.9ebd742b.css" rel="stylesheet">
    <script id="cf-turnstile-script" src="assets/login/api.js.Без названия" defer="" async=""></script>
    <link href="config/config.css" rel="stylesheet">
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
        .toast.error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
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
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
        
        .fa-user-headset:before { content: "\f84f"; }
        .fa-money-bills:before { content: "\e1f3"; }
        .fa-bag-shopping:before { content: "\f290"; }
        .fa-right-to-bracket:before { content: "\f2f6"; }
        
        .fa-solid {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-style: normal;
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
                            <button type="button" id="react-aria8365650789-:r0:" aria-expanded="false" class="NavBar_languageSelector__0l8kx dropdown-toggle btn">
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
                <h4>Авторизация</h4>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="input-group input-group-lg mb-2 mt-4">
                        <input type="text" name="login" class="form-control <?php echo in_array('Логин или email не может быть пустым', $errors) ? 'error' : ''; ?>" placeholder="Введите логин или e-mail" maxlength="50" value="<?php echo $success ? '' : htmlspecialchars($login ?? ''); ?>">
                    </div>
                    <div class="input-group input-group-lg mb-4">
                        <input type="password" name="password" class="form-control <?php echo in_array('Пароль не может быть пустым', $errors) ? 'error' : ''; ?>" placeholder="Введите пароль" maxlength="100" value="">
                    </div>
                    <div class="d-flex justify-content-between w-100">
                        <div class="form-group">
                            <label class="checkbox-container">
                                Запомнить меня
                                <input type="checkbox" name="remember" value="1" <?php echo ($success || !isset($_POST['remember'])) ? '' : 'checked'; ?>>
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <a class="Auth_authLink__tBmtP" href="changepass.php">Забыли пароль?</a>
                    </div>
                    <div id="cf-turnstile" style="width: 300px; height: 65px;">
                        <div>
                            <template shadowrootmode="closed">
                                <iframe src="https://challenges.cloudflare.com/cdn-cgi/challenge-platform/h/b/turnstile/if/ov2/av0/rcv/iooog/0x4AAAAAAAB5MBip7H_Q8dlq/dark/fbE/new/normal/auto/" allow="cross-origin-isolated; fullscreen; autoplay" sandbox="allow-same-origin allow-scripts allow-popups" id="cf-chl-widget-iooog" tabindex="0" title="Виджет с действием &quot;challenge&quot; Cloudflare" style="border: none; overflow: hidden; width: 300px; height: 65px;"></iframe>
                            </template>
                            <input type="hidden" name="cf-turnstile-response" id="cf-chl-widget-iooog_response" value="0.Q6rrlBSZq_XTfIIcrC-p1CgEvPZqu6f-jwWXo9dNSHoROhWRf4IViuOuBtA8O92ioemSLDerYYnRgfGxDVPfIyrgH6b_324cZUqmXX7x_c66Z88mACDPqE5mkFOO8zpHhmyaktAluIm_E9eWCGQRHmJ-63a6hn3h4KfMovBnDdxLFPnNUyCASrthzserZdDCdgm3Pcx2J8YLjmG4wthhVh-Ulzc19tSRIO7n10cK0zPCWQHpSS3fB7g8vn74ir8l7hJEy4wCOqD7EV-iN3LJCxW_cc-SmtlveojPehFi9jN5nZEqrNt8uhvr3IQspPd07AVFLIDWNk1cFbXKaWgBVAeoW1c3Di9FgA8adinL6vdaZ6r71-cCxu9eETrctbq0UfBKbdYe5XMbm6gn9zxg77ZOCz-PWggldxZPuEM1ZkdrZE81Y9ojrEarCOwkaPm7XvHI7fsZImrh1EjE9ZmNYyTeVjnNe3qJ1jvemhrvPIulAfy6urJ0f7sdXIayyxv8Z5Fs5kvn0k4RMWLfD-2pyq1uhiznfXxxb7BYSIrDiY5Y13-eVhwXeDz28cs3T-Bpr-LrsaHK_MhRF-6foHpFRdYDoquOQfymg0xSiqJ1728UeiE_r3ZOLC40V0PQ6rIL3nNrm2S8GZ7KyQkfi0Vtov1AZ56VAFNLUvoy38HkkeZhjndPcEFMG_dfNVzCbxkQ-UXjL4wo68tY3cRqjnyXGkSUyFlbfRTRigfx_Kenw8OIjfnwpOxvhEDMtEfIvTQp_dDXf8pLw1O7PEYDCR1WwKfdjVZtZiQADwU5Wgaes_XFF8wnSpVhcy5mfKCw_jyH7Sao1xBF10Q8umMev6Ke7kyKbtpSNt4R20OECQwlpeecpg2-oU-kIx99VD-Ni9MkvvN1CWK1XIIAmJok4bX-rE8pVbFfnxnMsl4CX65UOY4.jxc_NbdEJyMuKnGOVWBJig.ec6c8120f1eebfda7af3c1a6271ec01144c83f1ca8d0d8864a69089a5e4a6165">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-lg btn-gradient w-100 mt-3">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>Войти
                    </button>
                </form>
                <div class="button-group d-flex flex-wrap align-items-center justify-content-center w-100 mt-4">
                    <a class="Auth_authLink__tBmtP" href="register.php">Регистрация</a>
                </div>
            </div>
        </div>
          <div class="Footer_footer__gtV2S">
            <div class="container-xl">
                <div class="Footer_logo__C5wy8"><?=$nameClient ?></div>
                <div class="Footer_links__7+5fr">
                    <div class="Footer_leftLinks__9B3S6">
                        <span class="mb-2">Навигация</span>
                        <a class="Footer_link__QicBf" href="index.php">Главная</a>
                        <a class="Footer_link__QicBf" href="">Купить</a>
                        <a class="Footer_link__QicBf" href="<?=$discordLink?>" target="_blank" rel="noreferrer">Поддержка</a>
                    </div>
                    <div class="Footer_centerLinks__r0miB">
                        <span></span>
                        <span>Почта для связи: ntfhelp@mail.ru (ПН-ПТ 10:00-18:00 МСК)</span>
                    </div>
                    <div class="Footer_rightLinks__pqAM1">
                        <span class="mb-2">Навигация</span>
                        <a class="Footer_link__QicBf" href="footer/personal.php">Обработка персональных данных</a>
                        <a class="Footer_link__QicBf" href="footer/elua.php">Пользовательское соглашение</a>
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
    
    <?php if (!empty($errors) && !$success): ?>
        <div class="toast error" id="errorToast">
            <i class="fa-solid fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($errors[0]); ?>
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
        
        <?php if (!empty($errors) && !$success): ?>
            setTimeout(function() {
                document.getElementById('errorToast').classList.add('show');
                setTimeout(function() {
                    document.getElementById('errorToast').classList.remove('show');
                }, 4000);
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
        })()
    </script>
    <iframe height="1" width="1" style="position: absolute; top: 0px; left: 0px; border: none; visibility: hidden;" src="assets/login/saved_resource.html"></iframe>
</body>
</html>