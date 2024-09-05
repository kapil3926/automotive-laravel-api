<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@200;300;400;500;600;700;800;900&family=Roboto:wght@100&family=Source+Serif+Pro:wght@200;300;400;600&display=swap"
          rel="stylesheet">
    <title>Verification</title>

    <style>

        html, body {
            font-family: 'Open Sans', sans-serif;
            width: 100%;
            background-color: #eee;
        }

        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            scroll-behavior: smooth;
            transition: all;
        }

        a {
            text-decoration: none;
        }

        h2 {
            font-size: 2rem;
            font-weight: 700;
            margin: 1rem 0;
            color: #565656;
        }

        h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 1rem 0;
            color: #565656;
        }

        h6 {
            font-size: 1.5rem;
            font-weight: 400;
            margin: 1rem 0;
            color: #565656;
        }

        p {
            font-size: 1rem;
            font-weight: 600;
            margin: 1rem 0;
            color: #565656;
        }

        .logo-wrapper {
            background-color: #FFF4E8;
            padding: 3rem;
            position: relative;
        }

        .logo-wrapper .logo img {
            width: 15rem
        }

        .logo-wrapper h2 {
            position: relative;
            text-align: center;
            color: #fff;
            top: -2rem;
        }

        .vfrecip-wrapper {
            background-color: #eee;

            height: auto;
        }

        .vf-wrapper {
            background-color: #fff;
            padding: 2rem;
            width: 100%;
            max-width: 50%;
            height: 100%;
            max-height: 40%;
            display: block;
            margin: auto;
            position: relative;

            z-index: 99;
        }

        .vf-wrapper h3 {
            text-align: center;
            border: 1px solid #FF8707;
            padding: 1em;
            border-radius: 24px;
            margin: 1rem 0;
        }

        .vf-wrapper h6 {
            font-weight: 700;
        }

        .links-wrapper {

            padding: 1rem;


        }

        .links-wrapper p {
            text-align: center;
        }

        .links-wrapper a {
            color: #FF8707;
        }

        /* ----responsive */
        @media (max-width: 991px) {
            h2 {
                font-size: 1.5rem;

            }

            h3 {
                font-size: 1rem;

            }

            h6 {
                font-size: 1rem;

            }

            p {
                font-size: 1rem;

            }

            .logo-wrapper .logo img {
                width: 10rem
            }

            .vf-wrapper {
                width: 100%;
                max-width: 90%;
                height: 100%;
                max-height: 40%;
            }
        }


    </style>
</head>

<body>
<section class="logo-wrapper">
    <div>
        <h2 class="logo"><img src="https://automotive.virtualsecretary.in/admin/assets/images/logo/logo.png" alt="">
        </h2>
        <h2 class="logo" style="color: black">Old Automotive Parts</h2>
    </div>
</section>

<section class="vfrecip-wrapper">
    <section class="vf-wrapper" style="top: -4rem">
        <div>
            <h2>Token Verification Code For Reset Password!</h2>


            <p>

                We understand you'd like to change your password. Just Copy the below CODE.
            </p>

            <h6>
                Your Code:
            </h6>
            <h3>
                {{$link}}
            </h3>

            <p>If you did not ask for a password change, just ignore this email.

            </p>
            <h6>
                Thanks,
            </h6>

            <p>
                Old Automotive Parts
            </p>

        </div>
    </section>
    <section class="links-wrapper">
        <p>
            This message was mailed by Old Automotive Parts .
        </p>
        <!-- <p>
            For further assitance, you can reach out to us <a href="#">here</a>
        </p> -->
    </section>
</section>
</body>
</html>