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
        :root {
            --dark: #1B2533;
            --primaryColor: #2653C7;
        }
        .fcal_author_header {
            max-width: 420px;
            margin: auto;
            text-align: center;
        }
        .fcal_author_header img {
            width: 96px;
            height: 96px;
            object-fit: cover;
            border-radius: 8px;
            display: block;
            margin: auto auto 10px auto;
        }
        .fcal_author_header h1 {
            font-size: 20px;
            font-weight: 700;
            line-height: 28px;
            margin: 0;
            color: var(--dark);
        }
        .fcal_author_header p {
            font-size: 16px;
            font-weight: 500;
            line-height: 24px;
            margin: 8px 0 0 0;
        }
        .fcal_slots {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 32px;
        }
        .fcal_slots_wrap {
            border-top: 1px solid #D6DAE1;
            padding-top: 48px;
            margin-top: 48px;
        }
        .fcal_slots_wrap h1 {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            line-height: 32px;
            margin: 0 0 32px;
        }
        .fcal_slot {
            border: 1px solid #D6DAE1;
            padding: 24px;
            border-radius: 8px;
            transition: .3s;
        }
        .fcal_slot h2 {
            font-size: 18px;
            font-weight: 700;
            line-height: 28px;
            position: relative;
            margin: 0;
            color: var(--dark);
            padding-left: 32px;
        }
        .fcal_slot h2 .fcal_slot_color_schema {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            position: absolute;
            left: 0;
            top: 6px;
        }
        .fcal_slot .fcal_description {
            font-size: 16px;
            font-weight: 400;
            line-height: 24px;
            color: var(--dark);
            margin: 0 0 16px;
            padding-left: 32px;
        }

        .fcal_slot button {
            border: 1px solid var(--primaryColor);
            color: var(--primaryColor);
            width: 100%;
            font-size: 14px;
            font-weight: 500;
            line-height: 20px;
            display: block;
            border-radius: 8px;
            background: transparent;
            padding: 7px 24px;
            cursor: pointer;
            transition: .3s;
        }
        .fcal_slot button:hover {
            background: var(--primaryColor);
            border-color: var(--primaryColor);
            color: #ffffff;
        }

        .fcal_slot:hover {
            box-shadow: 0 8px 30px rgba(27, 37, 51, 0.1);
        }

        .fcal_slot > a.cal_card {
            text-decoration: none;
        }

        .fluent_booking_app {
            max-width: 1216px;
        }

        @media (max-width: 800px) {
            .fcal_slots {
                grid-template-columns: 1fr 1fr;
            }
            .fluent_booking_app {
                padding: 0 20px;
            }
        }
        @media (max-width: 544px) {
            .fcal_slots {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .fcal_slots_wrap {
                padding-top: 28px;
                margin-top: 28px;
            }
        }
    </style>
</head>
<body>

<div class="fcal_calendar_wrap">
    <div class="fluent_booking_app">
        <div class="fcal_author_header">
            <img src="<?php echo $author['avatar']; ?>"/>
            <div class="author_info">
                <h1>
                    <?php echo $author['name']; ?>
                </h1>
                <?php if ($calendar->description) { ?>
                    <p class="fcal_description"><?php echo $calendar->description; ?></p>
                <?php } ?>
            </div>
        </div>
        <div class="fcal_slots_wrap">
            <h1><?php esc_html_e('All Bookings', 'fluent-booking'); ?></h1>
            <div class="fcal_slots">
                <?php foreach ($slots as $slot): ?>
                <div class="fcal_slot">
                    <a href="<?php echo $slot->public_url; ?>" class="cal_card">
                        <h2>
                            <span class="fcal_slot_color_schema" style="background: <?php echo esc_attr($slot->color_schema); ?>;"></span>
                            <?php echo $slot->title; ?>
                        </h2>
                        <p class="fcal_description"><?php echo $slot->description; ?></p>
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
