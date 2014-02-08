<html>
    <head>
        <title>Load OpenLayers integration Sample Data</title>
        <style type="text/css">
            .error { color:red; }
        </style>
    </head>
    <body>
<?php

include(dirname(__FILE__)."/../../../mapadmin/constants.php");

try
{
MgInitializeWebTier(dirname(__FILE__)."/../../../webconfig.ini");

if (array_key_exists("USERNAME", $_POST) && array_key_exists("PASSWORD", $_POST)) {

    $siteConn = new MgSiteConnection();
    $userInfo = new MgUserInformation($_POST["USERNAME"], $_POST["PASSWORD"]);
    $siteConn->Open($userInfo);
    
    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
    
    //Commercial sample
    $res1 = new MgResourceIdentifier("Library://Samples/Sheboygan/Maps/SheboyganCommercial.MapDefinition");
    $bs1 = new MgByteSource(dirname(__FILE__)."/SheboyganCommercial.MapDefinition.xml");
    $br1 = $bs1->GetReader();
    $resSvc->SetResource($res1, $br1, null);
    
    //Mixed sample
    $res2 = new MgResourceIdentifier("Library://Samples/Sheboygan/Maps/SheboyganMixed.MapDefinition");
    $bs2 = new MgByteSource(dirname(__FILE__)."/SheboyganMixed.MapDefinition.xml");
    $br2 = $bs2->GetReader();
    $resSvc->SetResource($res2, $br2, null);
?>
    <p>Sample resources required for OpenLayers integration samples loaded.</p>
    <p><a href="../index.php">Return to samples</a></p>
<? } else { ?>
    <p>To load the sample resources required for OpenLayers integration samples, login as Administrator</p>
    <p><strong>NOTE: Make sure you have already downloaded the <a href="http://download.osgeo.org/mapguide/releases/2.0.0/samples/Sheboygan.mgp">Sheboygan Dataset</a> and load this in via the <a href="../../mapadmin/login.php">MapGuide Site Administrator</a> first before loading these OpenLayers integration sample resources</strong></p>
    <form action="load.php" method="post">
        Username: <input type="text" name="USERNAME" id="USERNAME" />
        <br/>
        Password: <input type="password" name="PASSWORD" id="PASSWORD" />
        <br/>
        <input type="submit" value="Login" />
    </form>
    <p><a href="../index.php">Return to samples</a></p>
<? } 
} catch (MgException $e) {
?>
    <p>An error occured:</p>
    <div class="error">
    <?= $e->GetDetails() ?>
    </div>
    <p><a href="javascript:history.go(-1)">Go back</a></p>
    <p><a href="../index.php">Return to samples</a></p>
<? } ?>

    </body>
</html>