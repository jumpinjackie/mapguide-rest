<html>
    <head>
        <title>Load OpenLayers integration Sample Data</title>
        <link rel="stylesheet" href="../../assets/common/css/bootstrap.min.css" />
        <style type="text/css">
            /* Move down content because we have a fixed navbar that is 50px tall */
            body {
                padding-top: 70px;
                padding-bottom: 20px;
            }
            .error { color:red; }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
          <div class="container">
            <div class="navbar-header">
              <a class="navbar-brand" href="../index.php">MapGuide REST Samples</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
              
            </div><!--/.navbar-collapse -->
          </div>
        </nav>
<?php

if (!class_exists("MgServiceType")) {
include(dirname(__FILE__)."/../../../mapadmin/constants.php");
}

try
{
MgInitializeWebTier(dirname(__FILE__)."/../../../webconfig.ini");

if (array_key_exists("USERNAME", $_POST) && array_key_exists("PASSWORD", $_POST)) {

    $siteConn = new MgSiteConnection();
    $userInfo = new MgUserInformation($_POST["USERNAME"], $_POST["PASSWORD"]);
    $siteConn->Open($userInfo);

    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
    $featSvc = $siteConn->CreateService(MgServiceType::FeatureService);

    //Commercial sample
    $res1 = new MgResourceIdentifier("Library://Samples/Sheboygan/MapsTiled/SheboyganNoWatermark.MapDefinition");
    $bs1 = new MgByteSource(dirname(__FILE__)."/SheboyganNoWatermark.MapDefinition.xml");
    $br1 = $bs1->GetReader();
    $resSvc->SetResource($res1, $br1, null);

    //Mixed sample
    $res2 = new MgResourceIdentifier("Library://Samples/Sheboygan/Maps/SheboyganMixed.MapDefinition");
    $bs2 = new MgByteSource(dirname(__FILE__)."/SheboyganMixed.MapDefinition.xml");
    $br2 = $bs2->GetReader();
    $resSvc->SetResource($res2, $br2, null);

    //Enable writeable parcels
    $parcelsId = new MgResourceIdentifier("Library://Samples/Sheboygan/Data/Parcels.FeatureSource");
    $writeParcelsId = new MgResourceIdentifier("Library://Samples/Sheboygan/Data/Parcels_Writeable.FeatureSource");
    if ($resSvc->ResourceExists($parcelsId)) {
        $resSvc->CopyResource($parcelsId, $writeParcelsId, true);

        $bsWriteable = new MgByteSource(dirname(__FILE__)."/Parcels_Writeable.FeatureSource.xml");
        $brWriteable = $bsWriteable->GetReader();
        $resSvc->SetResource($writeParcelsId, $brWriteable, null);
    }

    //Set up comments data store
    $commentsId = new MgResourceIdentifier("Library://Samples/Sheboygan/Data/ParcelComments.FeatureSource");
    if ($resSvc->ResourceExists($commentsId)) {
        $resSvc->DeleteResource($commentsId);
    }
    $schema = new MgFeatureSchema("Default", "Default schema");
    $clsDef = new MgClassDefinition();
    $clsDef->SetName("ParcelComments");
    $props = $clsDef->GetProperties();
    $idProps = $clsDef->GetIdentityProperties();

    $id = new MgDataPropertyDefinition("ID");
    $id->SetDataType(MgPropertyType::Int32);
    $id->SetNullable(false);
    $id->SetAutoGeneration(true);

    $pid = new MgDataPropertyDefinition("ParcelID");
    $pid->SetDataType(MgPropertyType::Int32);
    $pid->SetNullable(false);
    $pid->SetLength(255);

    $name = new MgDataPropertyDefinition("Name");
    $name->SetDataType(MgPropertyType::String);
    $name->SetNullable(true);
    $name->SetLength(255);

    $comment = new MgDataPropertyDefinition("Comment");
    $comment->SetDataType(MgPropertyType::String);
    $comment->SetNullable(true);
    $comment->SetLength(255);

    $recordedDate = new MgDataPropertyDefinition("RecordedDate");
    $recordedDate->SetDataType(MgPropertyType::DateTime);
    $recordedDate->SetNullable(false);

    $props->Add($id);
    $idProps->Add($id);

    $props->Add($pid);
    $props->Add($name);
    $props->Add($comment);
    $props->Add($recordedDate);

    $classes = $schema->GetClasses();
    $classes->Add($clsDef);

    $createParams = new MgFileFeatureSourceParams("OSGeo.SDF");
    $createParams->SetFeatureSchema($schema);

    $featSvc->CreateFeatureSource($commentsId, $createParams);

    //Web Layout demonstrating intergration with REST-enabled published data
    $bs3 = new MgByteSource(dirname(__FILE__)."/RESTWebLayout.mgp");
    $br3 = $bs3->GetReader();
    $resSvc->ApplyResourcePackage($br3);
?>
    <div class="container">
        <p>Sample resources required for OpenLayers integration samples loaded.</p>
        <p><a class="btn btn-primary" href="../index.php">Return to samples</a></p>
    </div>
<?php } else { ?>
    <div class="container">
        <div class="well">
            <p>To load the sample resources required for OpenLayers integration samples, login as Administrator</p>
        </div>
        <div class="alert alert-info">Make sure you have already downloaded the <a href="http://download.osgeo.org/mapguide/releases/2.0.0/samples/Sheboygan.mgp">Sheboygan Dataset</a> and load this in via the <a href="../../../mapadmin/login.php">MapGuide Site Administrator</a> first before loading these OpenLayers integration sample resources</div>
        <form action="load.php" method="post">
            <div class="form-group">
                <label for="USERNAME">Username:</label>
                <input type="text" class="form-control" name="USERNAME" id="USERNAME" />
            </div>
            <div class="form-group">
                <label for="PASSWORD">Password:</label>
                <input type="password" class="form-control" name="PASSWORD" id="PASSWORD" />
            </div>
            <input type="submit" class="btn btn-primary" value="Login" />
            <a class="btn btn-primary" href="../index.php">Return to samples</a>
        </form>
    </div>
<?php }
} catch (MgException $e) {
?>
    <div class="alert alert-danger">
        <p>An error occured:</p>
        <pre>
    <?= $e->GetDetails() ?>
        </pre>
    </div>
    <p><a href="javascript:history.go(-1)">Go back</a></p>
    <p><a href="../index.php">Return to samples</a></p>
<?php } ?>

    </body>
</html>
