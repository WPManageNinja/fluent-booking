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
        <link rel="stylesheet" href="<?php echo $css_file; ?>?version=<?php echo FLUENT_BOOKING_ASSETS_VERSION; ?>" media="screen"/>
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
            grid-template-columns: 1fr;
            border: 1px solid #D6DAE1;
            border-radius: 8px;
            overflow:hidden;
        }
        .fcal_slots_wrap {
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
            border-bottom: 1px solid #D6DAE1;
            padding: 16px 24px;
            transition: .3s;
        }
        .fcal_slots .fcal_slot:last-child {
            border-bottom: none;
        }
        .fcal_slot h2 {
            font-size: 16px;
            font-weight: 700;
            line-height: 24px;
            position: relative;
            margin: 0;
            color: var(--dark);
            padding-left: 19px;
        }
        .fcal_slot h2 .fcal_slot_color_schema {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            position: absolute;
            left: 0;
            top: 8px;
        }
        .fcal_slot .fcal_description {
            font-size: 16px;
            font-weight: 400;
            line-height: 24px;
            color: var(--dark);
            margin: 0 0 7px;
            padding-left: 19px;
        }

        .fcal_slot .fcal_slot_duration {
            font-size: 12px;
            font-weight: 500;
            line-height: 18px;
            margin: 0;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #445164;
            padding-left: 19px;
        }
        .fcal_slot button {
            border: 1px solid #D6DAE1;
            color: var(--dark);
            font-size: 14px;
            font-weight: 500;
            line-height: 20px;
            display: inline-flex;
            align-items: center;
            border-radius: 8px;
            background: transparent;
            padding: 7px 16px 7px 16px;
            cursor: pointer;
            transition: .3s;
            gap: 8px;
            position: relative;
        }
        .fcal_slot button svg {
            transition: .3s;
            position: absolute;
            right: 16px;
            opacity: 0;
            visibility: hidden;
        }
        .fcal_slot button:hover {
            color: var(--primaryColor);
            border-color: var(--primaryColor);
            padding-right: 38px;
        }
        .fcal_slot button:hover svg {
            opacity: 1;
            visibility: visible;
        }

        .fcal_slot:hover {
            background: #F6F6F7;
        }

        .fcal_slot > a.fcal_card {
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fluent_booking_app {
            max-width: 750px;
        }

        @media (max-width: 800px) {
            .fluent_booking_app {
                padding: 0 20px;
            }
        }
        @media (max-width: 500px) {
            .fcal_slots_wrap {
                margin-top: 28px;
            }
            .fcal_slot > a.fcal_card {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .fcal_slot button {
                left: 19px;
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
            <div class="fcal_slots">
                <?php foreach ($events as $event): ?>
                <div class="fcal_slot">
                    <a href="<?php echo $event->public_url; ?>" class="fcal_card">
                        <div class="fcal_slot_content">
                            <h2>
                                <span class="fcal_slot_color_schema" style="background: <?php echo esc_attr($event->color_schema); ?>;"></span>
                                <?php echo $event->title; ?>
                            </h2>
                            <p class="fcal_description"><?php echo $event->description; ?></p>
                            <span class="fcal_slot_duration">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M12.8334 7C12.8334 10.22 10.22 12.8333 7.00002 12.8333C3.78002 12.8333 1.16669 10.22 1.16669 7C1.16669 3.78 3.78002 1.16666 7.00002 1.16666C10.22 1.16666 12.8334 3.78 12.8334 7Z" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.16418 8.855L7.35585 7.77584C7.04085 7.58917 6.78418 7.14 6.78418 6.7725V4.38084" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <?php echo esc_attr($event->duration); esc_html_e(' minutes', 'fluent-booking'); ?>
                            </span>
                        </div>
                        <button class="book_now">
                            Book Now <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                                <path d="M12.025 5.44167L17.0833 10.5L12.025 15.5583" stroke="#306AE0" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2.91666 10.5H16.9417" stroke="#306AE0" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
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
