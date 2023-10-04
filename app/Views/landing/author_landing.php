<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo esc_attr($title); ?></title>
    <meta charset='utf-8'>

    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta content='yes' name='apple-mobile-web-app-capable'>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta name="robots" content="noindex"/>

    <link rel="icon" type="image/x-icon" href="<?php echo $author['avatar']; ?>">

    <meta property="og:title" content="<?php echo esc_attr($title); ?>" />
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url($url); ?>"/>
    <meta property="og:site_name" content="ConvertLeap">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>"/>
    <meta property="og:author" content="<?php echo $author['name']; ?>"/>

    <meta property="og:image" content="<?php echo FLUENT_BOOKING_URL; ?>assets/images/default-featured.png" />

    <?php foreach ($css_files as $css_file): ?>
        <link rel="stylesheet" href="<?php echo $css_file; ?>" media="screen"/>
    <?php endforeach; ?>

    <style>
        .author_header {
            padding: 20px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        .author_header img {
            max-width: 96px;
            border-radius: 50%;
        }
        .author_header h1 {
            font-size: 24px;
            margin: 0 0 10px;
        }
        .cal_slots {
            padding: 20px 30px;
            display: flex;
            flex-wrap: wrap;
            max-width: 900px;
            margin: 0 auto;
        }
        .cal_slot {
            flex: 0 calc(50% - 40px);
            margin: 20px;
            background: #f8fafc;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            box-shadow: 0 1px 6px 0 rgb(0 0 0 / 10%);
        }
        .cal_slot h2 {
            font-size: 18px;
            margin: 0 0 10px;
        }
        .cal_slot .cal_description {
            font-size: 14px;
            color: #666;
            min-height: 80px;
        }

        .cal_slot button {
            border: 1px solid #666;
            padding: 5px 15px;
            background: white;
            cursor: pointer;
        }

        .cal_slot:hover {
            box-shadow: 0 2px 12px 0 rgb(0 0 0 / 15%);
        }

        .cal_slot > a.cal_card {
            color: initial;
            text-decoration: none;
            padding: 20px;
            display: block;
        }

        .fluent_booking_app {
            max-width: 900px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="calendar_wrap">
    <div class="fcal_cal_wrap">
        <div class="fluent_booking_app">
            <div class="author_header">
                <img src="<?php echo $author['avatar']; ?>"/>
                <div class="author_info">
                    <h1><?php echo $author['name']; ?></h1>
                    <div class="cal_description"><?php echo $calendar->description; ?></div>
                </div>
            </div>
            <div class="cal_slots">
                <?php foreach ($slots as $slot): ?>
                <div class="cal_slot">
                    <a href="<?php echo $slot->public_url; ?>" class="cal_card">
                        <h2><?php echo $slot->title; ?></h2>
                        <div class="cal_description"><?php echo $slot->description; ?></div>
                        <button class="book_now">
                            Book Now
                        </button>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
