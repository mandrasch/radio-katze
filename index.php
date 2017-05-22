
<!DOCTYPE HTML>
<!--
    Identity by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
    <head>
        <title>RADIO KATZE</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <!--[if lte IE 8]><script src="html5up-identity/assets/js/html5shiv.js"></script><![endif]-->
        <link rel="stylesheet" href="html5up-identity/assets/css/main.css" />
        <!--[if lte IE 9]><link rel="stylesheet" href="html5up-identity/assets/css/ie9.css" /><![endif]-->
        <!--[if lte IE 8]><link rel="stylesheet" href="html5up-identity/assets/css/ie8.css" /><![endif]-->
        <noscript><link rel="stylesheet" href="html5up-identity/assets/css/noscript.css" /></noscript>


<?php
include 'vendor/autoload.php';

use MPDWrapper\SimpleMPDWrapper;
use RestService\Server;

$stations = array(
    'FM4' => 'http://mp3stream1.apasf.apa.at',
    'Detektor FM Musik' => 'http://stream.hoerradar.de/detektorfm-musik-mp3-128',
    'Fritz' => 'http://www.fritz.de/live.m3u',
	'Deutschlandradio Wissen' => 'http://www.dradio.de/streaming/dradiowissen_hq_ogg.m3u',
    );

    // 2DO string validation actions
    $action = isset($_GET["action"]) ?  filter_var($_GET["action"], FILTER_SANITIZE_STRING) : '';
    $old_log = isset($_GET["log"]) ? urldecode(filter_var($_GET["log"], FILTER_SANITIZE_STRING)) : '';

    // CONFIG
    $mpd_address = "localhost"; // can be an ip address as well
    $mpd_port = 6600;
    $mpd_password = "";

    $mpd = new SimpleMPDWrapper($mpd_password,$mpd_address,$mpd_port,0);

    $log = '';

    // 2DO: error handling if there is an error message?

    switch($action){
        case 'play':

            $response = $mpd->send("clear");
            $log .= print_r($response,true);

            /* only for debug reasons
            $response = $mpd->send("playlist");
            $log .= print_r($response,true);*/

            //2DO: string validation number
            $number = filter_var($_GET["station"], FILTER_SANITIZE_STRING);

            $station_address = array_values($stations)[$number]; //2DO: error handling
            $log .= "Station address: ".$station_address." <br>";

            // check wheter it is m3u or just a URL
            if(substr(strtolower($station_address), -3) == 'm3u'){
                $response = $mpd->send("load",$station_address);
                // 2DO: error handling
                $log .= print_r($response,true);
            }
            else{
                $response = $mpd->send("add",$station_address);
                //2DO: error handling
                $log .= print_r($response,true);
            }

            /* only for debug reasons */
            $response = $mpd->send("playlist");
            $log .= print_r($response,true);

            $response = $mpd->send("play", "0");
            $log .= print_r($response,true);

            // dirty, usually via AJAX ;)
            header('Location: '.$_SERVER['PHP_SELF']."?station=".$number."&log=".urlencode($log));
        break;
    case 'stop':
        $response = $mpd->send("stop");
        $log .= print_r($response,true);
        break;
    case 'play':
            $response = $mpd->send("play", "0");
            $log .= print_r($response,true);
        break;
    case 'pause':
            $response = $mpd->send("pause");
            $log .= print_r($response,true);
        break;
    case 'mpd-restart':
        break;
    case 'shutdown':
        // shutdown trick: http://stackoverflow.com/questions/5226728/how-to-shutdown-ubuntu-with-exec-php
        // you have to use a cronjob
        $file = fopen('.shutdown',"w");
        fwrite($file, 'shutdown');
        close($file);
        echo "Server wird heruntergefahren... ";
        die();
        break;
    case 'refresh-title':
    default:
        // does not work? unknown command? works on terminal?
        // $response = $mpd->send("current");
        // $log .= print_r($response,true);
        break;
    }

    // currrent station - nicer way would be with mpc current, but this does not work right now
    // 2DO: validate get_var

    $station_number = isset($_GET['station']) ? filter_var($_GET["station"], FILTER_SANITIZE_NUMBER_INT) : FALSE;
    $station_title = $station_number ? array_keys($stations)[$station_number] : '';
?>

<?php    
    /* bonus: random cat background image: */
    $directory = "pixabay-cats/";
    $images = glob($directory . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $randomCat = $images[array_rand($images)];
?>


</head>

<body class="is-loading">

        <!-- Wrapper -->
            <div id="wrapper">

                <!-- Main -->
                    <section id="main">
                        <header>
                            <h1>RADIO KATZE</h1>
                            <p>Now playing: <?php echo $station_title; ?>
                                <a href='index.php?action=play'>Play</a> | <a href='index.php?action=stop'>Stop</a> | <a href='index.php?action=shutdown'>Shutdown</a> 
                            </p>
                        </header>
                        <p><img src="<?php echo $randomCat; ?>" style="max-width:450px;" /><br>Pixabay-Cats (CC0 license)</p>

                        <h2>Stations</h2>
                        <ul>
                        <?php
                        $i = 0;
                        foreach($stations as $title => $url)
                        {
                            echo "<li><a href='index.php?action=play&station=".$i."'>".$title."</a></li>";
                            $i++;
                        }
                        ?>
                        </ul>

                        <hr />
                        <h2>Log</h2>
                        <textarea><?php echo $old_log; ?></textarea>
                        <footer>
                            <ul class="icons">
                                <li><a href="#" class="fa-twitter">Twitter</a></li>
                                <li><a href="#" class="fa-instagram">Instagram</a></li>
                                <li><a href="#" class="fa-facebook">Facebook</a></li>
                            </ul>
                        </footer>
                    </section>

                <!-- Footer -->
                    <footer id="footer">
                        <ul class="copyright">
                            <li>Cats from Pixabay (CC0 license)</li><li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
                        </ul>
                    </footer>

            </div>

        <!-- Scripts -->
            <!--[if lte IE 8]><script src="assets/js/respond.min.js"></script><![endif]-->
            <script>
                if ('addEventListener' in window) {
                    window.addEventListener('load', function() { document.body.className = document.body.className.replace(/\bis-loading\b/, ''); });
                    document.body.className += (navigator.userAgent.match(/(MSIE|rv:11\.0)/) ? ' is-ie' : '');
                }
            </script>
    </body>
</html>

