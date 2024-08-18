<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Email Verification</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">

    <!-- CSS Reset -->
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            background: #f1f1f1;
        }

        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 auto;
            width: 100%;
        }

        img {
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
            height: auto;
            display: block;
            margin: auto;
        }

        a {
            text-decoration: none;
            color: #30e3ca;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Responsive Fixes */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .hero img {
                width: 100% !important;
                height: auto !important;
            }

            .text {
                padding: 0 1em !important;
            }
        }
    </style>

    <!-- Progressive Enhancements -->
    <style>
        .primary {
            background: #30e3ca;
        }

        .bg_white {
            background: #ffffff;
        }

        .btn {
            padding: 10px 15px;
            display: inline-block;
            border-radius: 5px;
            color: #ffffff;
        }

        .btn-primary {
            background: #30e3ca;
        }

        .btn-white {
            background: #ffffff;
            color: #000000;
        }

        .btn-white-outline {
            background: transparent;
            border: 1px solid #fff;
            color: #fff;
        }

        .btn-black-outline {
            background: transparent;
            border: 2px solid #000;
            color: #000;
            font-weight: 700;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Lato', sans-serif;
            color: #000000;
            margin-top: 0;
            font-weight: 400;
        }

        body {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 15px;
            line-height: 1.8;
            color: rgba(0, 0, 0, 0.4);
        }

        .logo h1 a {
            color: #30e3ca;
            font-size: 24px;
            font-weight: 700;
        }

        .hero {
            text-align: center;
        }

        .text h2 {
            color: #000;
            font-size: 40px;
            margin-bottom: 0;
            font-weight: 400;
            line-height: 1.4;
        }

        .text h3 {
            font-size: 24px;
            font-weight: 300;
        }

        .text h2 span {
            font-weight: 600;
            color: #30e3ca;
        }

        .heading-section h2 {
            color: #000000;
            font-size: 28px;
            margin-top: 0;
            line-height: 1.4;
            font-weight: 400;
        }

        .heading-section .subheading {
            margin-bottom: 20px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(0, 0, 0, 0.4);
        }

        .heading-section .subheading::after {
            content: '';
            width: 100%;
            height: 2px;
            background: #30e3ca;
            display: block;
            margin-top: 10px;
        }

        .footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            color: rgba(0, 0, 0, 0.5);
        }

        .footer .heading {
            color: #000;
            font-size: 20px;
        }

        .footer ul {
            margin: 0;
            padding: 0;
        }

        .footer ul li {
            list-style: none;
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: rgba(0, 0, 0, 1);
        }
    </style>

</head>

<body style="background-color: #f1f1f1; margin: 0; padding: 0;">
    <center style="width: 100%; background-color: #f1f1f1;">
        <div class="email-container">
            <!-- Header -->
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td valign="top" class="bg_white" style="padding: 1em 2.5em 0;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td class="logo" style="text-align: center;">
                                    <h1><a href="#">Showtime</a></h1>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Hero Image -->
                {{-- <tr>
                    <td valign="middle" class="hero bg_white" style="padding: 3em 0;">
                        <img src="{{ url('resource_images/email.png')}}" alt="Email Verification">
                        <img src="{{ url('resource_images/email.png') }}" alt="Email Verification" width="200" height="100">

                    </td>
                </tr> --}}
                <!-- Main Content -->
                <tr>
                    <td valign="middle" class="bg_white" style="padding: 2em 0;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td>
                                    <div class="text" style="padding: 0 2.5em; text-align: center;">
                                        <h2>Please verify your account</h2>
                                        <h4>{{ $details['message'] }}</h4>
                                        <h3><strong>{{ $details['otp_code'] }}</strong></h3>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>

                   
                </tr>
            </table>
        </div>
    </center>
</body>
</html>
