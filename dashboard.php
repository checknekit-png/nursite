<?php 
session_start();
include "config/config.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT u.*, s.subscription_name, s.status, s.expires_at as sub_expires_at FROM users u LEFT JOIN subscriptions s ON u.id = s.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

if ($user['is_banned']) {
    echo "Ваш аккаунт заблокирован. Причина: " . $user['ban_reason'];
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['activate_key'])) {
        $key = $_POST['key'];
        
        $stmt = $pdo->prepare("SELECT * FROM activation_keys WHERE key_value = ? AND is_used = 0 AND expires_at > NOW()");
        $stmt->execute([$key]);
        $activation_key = $stmt->fetch();
        
        if ($activation_key) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE activation_keys SET is_used = 1 WHERE id = ?");
                $stmt->execute([$activation_key['id']]);
                
                $new_expires_at = date('Y-m-d H:i:s', strtotime('+' . $activation_key['duration_days'] . ' days'));
                
                $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $existing_sub = $stmt->fetch();
                
                if ($existing_sub) {
                    if (strtotime($existing_sub['expires_at']) > time()) {
                        $new_expires_at = date('Y-m-d H:i:s', strtotime($existing_sub['expires_at'] . ' +' . $activation_key['duration_days'] . ' days'));
                    }
                    $stmt = $pdo->prepare("UPDATE subscriptions SET subscription_name = ?, status = 'active', expires_at = ? WHERE user_id = ?");
                    $stmt->execute([$activation_key['subscription_name'], $new_expires_at, $user_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, subscription_name, status, expires_at) VALUES (?, ?, 'active', ?)");
                    $stmt->execute([$user_id, $activation_key['subscription_name'], $new_expires_at]);
                }
                
                $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, activity_type) VALUES (?, ?)");
                $stmt->execute([$user_id, 'Активация ключа: ' . $key]);
                
                $pdo->commit();
                $success_message = "Ключ успешно активирован!";
                
                $stmt = $pdo->prepare("SELECT u.*, s.subscription_name, s.status, s.expires_at as sub_expires_at FROM users u LEFT JOIN subscriptions s ON u.id = s.user_id WHERE u.id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
            } catch (Exception $e) {
                $pdo->rollback();
                $error_message = "Ошибка активации ключа";
            }
        } else {
            $error_message = "Неверный или уже использованный ключ";
        }
    }
    
    if (isset($_POST['create_key']) && $user['role'] === 'Администратор') {
        $subscription_name = $_POST['subscription_name'];
        $duration_days = (int)$_POST['duration_days'];
        $key_value = bin2hex(random_bytes(16));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $stmt = $pdo->prepare("INSERT INTO activation_keys (key_value, subscription_name, duration_days, expires_at) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$key_value, $subscription_name, $duration_days, $expires_at])) {
            $created_key = $key_value;
        }
    }
    
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit();
    }
}

$stmt = $pdo->prepare("INSERT INTO user_activity (user_id, activity_type) VALUES (?, 'Вход в личный кабинет')");
$stmt->execute([$user_id]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="keywords" content="nursultan, nursultan nextgen, nurik, wexside, celestial, akrien, minecraft, minecraft 1.8, minecraft 1.12.2, minecraft 1.16.5, deadcode, нурсултан, nursultan client, нурсултан клиент, нурик, вексайд, целестиал, акриен, майнкрафт, чит, читы, читы для майнкрафт, читы для minecraft, чит на майнкрафт, чит на minecraft">
    <meta name="description" content=" Nursultan client - Лучший клиент для комфортной игры. Для майнкрафт 1.16.5 ">
    <title id="title"><?=$nameClient ?> - Лучший клиент для комфортной игры.</title>
    <link href="assets/dashboard/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="canonical" href="https://nursultan.fun/?lang=ru">
    <link href="assets/dashboard/main.9ebd742b.css" rel="stylesheet">
    <script id="cf-turnstile-script" src="assets/dashboard/api.js" defer="" async=""></script>
    <link href="config/config.css" rel="stylesheet">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .Cabinet_itemInput__sxPd- {
            display: flex;
            gap: 10px;
            width: 100%;
        }
        .Cabinet_itemInput__sxPd- .form-control {
            flex: 1;
        }

    </style>
</head>
<body>
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
                            <button type="button" class="NavBar_languageSelector__0l8kx dropdown-toggle btn">
                                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAqElEQVR4Xu2aoQ1CYRjEPsskbMASOPZhDzwrPIlhCYIgwaPeBj/+boS2SU3l6ZsRERERESj3x2cjO/u+L7IOkIGmA2Sg6QAZaDpABpoOkIGmA2SgOa/vb5GduWwLbQWaFWhWoFmBZgWaFWhWoFmBZgWaFWhWoFmBZgWaz8NpkZ33HBdZB8hA0wEy0HSADDQdIANNB8hA0wEy0Jzb+bqRzeuwiIiIiFD4AyiolWWRAEPWAAAAAElFTkSuQmCC" alt="">
                                Русский
                            </button>
                        </div>
                    </div>
                    <div class="ml-auto navbar-nav">
                        <a class="nav-link" href="<?=$discordLink?>" target="_blank" rel="noreferrer">
                            <i class="fa-solid fa-headset mr-2"></i>Поддержка
                        </a>
                        <a class="nav-link mr-2" href="product.php">
                            <i class="fa-solid fa-bag-shopping mr-2"></i>Купить
                        </a>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="logout" class="btn btn-lg btn-gradient">
                                <i class="fa-solid fa-sign-out-alt mr-2"></i>Выйти
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        
        <div class="Cabinet_content__bXwN9">
            <div class="toast-container"></div>
            <div class="Cabinet_container__146zz container-xl">
                <h3 class="mb-5">Личный кабинет</h3>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>
                
                <?php if (isset($created_key)): ?>
                    <div class="alert alert-success">
                        Ключ создан: <strong><?= $created_key ?></strong>
                    </div>
                <?php endif; ?>
                
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-fingerprint mr-2"></i>UID
                    </span>
                    <span><?= $user['id'] ?></span>
                </div>
                
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-tag mr-2"></i>Логин
                    </span>
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
                
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-gavel mr-2"></i>Группа
                    </span>
                    <span><?= htmlspecialchars($user['role']) ?></span>
                </div>
                
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-calendar-days mr-2"></i>Дата регистрации
                    </span>
                    <span><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></span>
                </div>
                
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-envelope mr-2"></i>E-mail
                    </span>
                    <span><?= htmlspecialchars($user['email']) ?></span>
                </div>
                
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-computer mr-2"></i>HWID
                    </span>
                    <div class="Cabinet_itemButtons__8aubB">
                        <span class="mr-auto"><?= $user['hwid'] ? htmlspecialchars($user['hwid']) : 'Неизвестно' ?></span>
                    </div>
                </div>
                
                <?php if ($user['subscription_name']): ?>
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-crown mr-2"></i>Подписка
                    </span>
                    <span><?= htmlspecialchars($user['subscription_name']) ?> 
                        <?php if ($user['sub_expires_at']): ?>
                            (до <?= date('d.m.Y H:i', strtotime($user['sub_expires_at'])) ?>)
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-key mr-2"></i>Активация ключа
                    </span>
                    <div class="Cabinet_itemInput__sxPd-">
                        <form method="POST" style="display: flex; gap: 10px; width: 100%;">
                            <input type="text" name="key" minlength="1" maxlength="100" class="form-control" placeholder="Введите ключ" value="" required style="flex: 1;">
                            <button type="submit" name="activate_key" class="btn btn-gradient">
                                <i class="fa-solid fa-unlock mr-2"></i>Активировать
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php if ($user['role'] === 'Администратор'): ?>
                <div class="Cabinet_item__4O9Kv">
                    <span>
                        <i class="fa-solid fa-plus mr-2"></i>Создание ключа
                    </span>
                    <div class="Cabinet_itemInput__sxPd-">
                        <form method="POST" style="display: flex; gap: 10px; width: 100%;">
                            <input type="text" name="subscription_name" class="form-control" placeholder="Название подписки" value="Премиум" required style="flex: 1;">
                            <input type="number" name="duration_days" class="form-control" placeholder="Дни" value="30" min="1" required style="width: 100px;">
                            <button type="submit" name="create_key" class="btn btn-gradient">
                                <i class="fa-solid fa-plus mr-2"></i>Создать
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="Cabinet_buttons__PbClP">
                    <a type="button" class="btn btn-gradient" href="product.php">
                        <i class="fa-solid fa-shopping-bag mr-2"></i>Купить клиент
                    </a>
                    <button type="button" class="btn btn-gradient">
                        <i class="fa-solid fa-percent mr-2"></i>Мои промокоды
                    </button>
                    <button type="button" class="btn btn-gradient">
                        <i class="fa-solid fa-shopping-bag mr-2"></i>Купленные товары
                    </button>
                    <button type="button" class="btn btn-gradient mr-xl-auto">
                        <i class="fa-solid fa-edit mr-2"></i>Сменить пароль
                    </button>
                    <?php if ($user['role'] === 'Администратор'): ?>
                    <button type="button" class="btn btn-gradient">
                        <i class="fa-solid fa-plus mr-2"></i>Создать ключ
                    </button>
                    <?php endif; ?>
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
                        <a class="Footer_link__QicBf" href="product.php">Купить</a>
                        <a class="Footer_link__QicBf" href="<?=$discordLink ?>" target="_blank" rel="noreferrer">Поддержка</a>
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
    

    
    <script>
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
    <iframe height="1" width="1" style="position: absolute; top: 0px; left: 0px; border: none; visibility: hidden;"></iframe>
</body>
</html>