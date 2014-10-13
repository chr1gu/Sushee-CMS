<?php
    require_once dirname(__FILE__) . '/../../src/tests/application.test.php';
    $test = new ApplicationTest();
    if (!empty($_GET) && isset($_GET['test'])) {
        header('Content-Type: application/json');
        $testResult = $test->test($_GET['test']);
        echo json_encode($testResult);
        exit;
    }
    $tests = $test->getTests();
?><!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js ie9" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 9]><!--> <html class="no-js" lang="en" itemscope itemtype="http://schema.org/Product"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title></title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="humans.txt">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <meta property="fb:page_id" content="" />
    <meta property="og:image" content="facebook.png" />
    <meta property="og:description" content=""/>
    <meta property="og:title" content=""/>
    <meta itemprop="name" content="">
    <meta itemprop="description" content="">
    <meta itemprop="image" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <link rel="stylesheet" href="css/gumby.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/libs/modernizr-2.6.2.min.js"></script>
</head>
<body class="metro">
    <div class="row">
        <h1>Applikations-Test</h1>
        <form>
            <fieldset>
                <legend><?php echo count($tests); ?> Test<?php echo count($tests)>1 ? 's' : ''; ?> vorhanden</legend>
                <ul>
                    <?php foreach($tests as $test): ?>
                    <li class="ttip" data-tooltip="<?php echo $test['description']; ?>" data-test="<?php echo $test['test']; ?>">
                        <i class="icon icon-right-circled"></i><?php echo $test['name']; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="medium rounded pull_right primary btn"><input type="submit" value="Test starten" /></div>
            </fieldset>
        </form>
    </div>
<script src="js/libs/jquery-2.0.2.min.js"></script>
<script src="js/libs/gumby.min.js"></script>
<script type="text/javascript">
    (function(){
        var testRunner = [
            <?php for($i = 0; $i < count($tests); $i++): ?>
            <?php echo "'" . $tests[$i]['test'] . "'" . ($i < count($tests)-1 ? ",\n" : "\n"); ?>
            <?php endfor; ?>
        ];
        var currentTest;
        var runTest = function (name) {
            var icon = $('[data-test="' + name + '"] .icon');
            icon.removeClass().addClass('icon icon-hourglass');
            $.get ('?test=' + name, function(data){
                var success = data.success;
                icon.removeClass('icon-hourglass').addClass(success ? 'icon-check' : 'icon-cancel');
                if (success) {
                    icon.parent ().append ('<span class="success label pull_right">' + (data.message || 'Erfolgreich') + '</span>');
                } else {
                    icon.parent ().append ('<span class="danger label pull_right">' + (data.message || 'Fehler') + '</span>');
                }
                if (currentTest < testRunner.length-1) {
                    currentTest++;
                    runTest (testRunner[currentTest]);
                } else {
                    var button = $('[type="submit"]');
                    button.prop("disabled", false).val('Nochmal');
                    button.parent().removeClass('default').addClass('primary');
                }
            });
        };
        $('form').submit(function(e){
            e.preventDefault();
            currentTest = 0;
            $('.icon').removeClass().addClass('icon icon-right-circled');
            $('.ttip .label').remove();
            runTest(testRunner[currentTest]);
            var button = $('[type="submit"]');
            button.prop("disabled", true).val('Wird geladen...');
            button.parent().removeClass('primary').addClass('default');
        });
    })();
</script>
</body>
</html>
