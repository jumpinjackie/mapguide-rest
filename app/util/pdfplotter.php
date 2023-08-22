<?php

require_once dirname(__FILE__)."/../util/utils.php";

class MgPdfPlotMetrics
{
    public $x;
    public $y;
    public $w;
    public $h;

    public function __construct($x, $y, $w, $h)
    {
        $this->x = $x;
        $this->y = $y;
        $this->w = $w;
        $this->h = $h;
    }
}

class MgPlotSize
{
    public $width;
    public $height;

    public function __construct($width, $height)
    {
        $this->width  = $width;
        $this->height = $height;
    }
}

class MgPdfPlotter
{
    private $paperSize;
    private $paperType;
    private $orientation;
    private $scaleDenominator;
    private $printSize;
    private $showLegend;
    private $showCoordinates;
    private $showDisclaimer;
    private $title;
    private $subTitle;
    private $dpi;
    private $legendDpi;

    private $handler;
    private $renderingService;

    private $bLayered;

    private $bDebug;

    const ORIENTATION_PORTRAIT = 'P';
    const ORIENTATION_LANDSCAPE = 'L';

    const CALIGN_CELL_TOP = 'T';
    const CALIGN_CELL_CENTER = 'C';
    const CALIGN_CELL_BOTTOM = 'B';
    const CALIGN_FONT_TOP = 'A';
    const CALIGN_FONT_BASELINE = 'L';
    const CALIGN_FONT_BOTTOM = 'D';

    const VALIGN_TOP = 'T';
    const VALIGN_CENTER = 'M'; //Docs say 'C', usage says 'M' ???
    const VALIGN_BOTTOM = 'B';

    public function __construct($handler, $renderingService, $map, $dpi = 96, $margin = NULL, $orientation = self::ORIENTATION_PORTRAIT, $paperType = 'A4', $showLegend = false, $showCoordinates = false, $showDisclaimer = false, $showScaleBar = false, $drawNorthArrow = false, $title = "", $subTitle = "") {
        $this->handler = $handler;
        $this->renderingService = $renderingService;
        $this->map = $map;

        $this->SetPaperType($paperType);
        $this->SetDisclaimer("");
        $this->SetOrientation($orientation);
        $this->scaleDenominator = $this->map->GetViewScale();

        $this->ShowLegend($showLegend);
        $this->ShowCoordinates($showCoordinates);
        $this->ShowDisclaimer($showDisclaimer);
        $this->ShowScaleBar($showScaleBar);
        $this->ShowNorthArrow($drawNorthArrow);
        $this->SetTitle($title);
        $this->SetSubTitle($subTitle);
        $this->dpi = $dpi;
        $this->legendDpi = 96;

        $env = $this->map->GetMapExtent();
        $ll = $env->GetLowerLeftCoordinate();
        $ur = $env->GetUpperRightCoordinate();
        $this->box = $ll->GetX().",".$ll->GetY().",".$ur->GetX().",".$ll->GetY().",".$ur->GetX().",".$ur->GetY().",".$ll->GetX().",".$ur->GetY().",".$ll->GetX().",".$ll->GetY();
        $this->normalizedBox = $this->box;

        //Not used in main plotting, but still used for north arrow rendering
        $this->rotation = 0;

        if ($margin != NULL)
            $this->margin = $margin;
        else
            $this->SetMargins(1.0, 0.5, 0.5, 0.5);

        $this->bLayered = false;
    }

    public function SetPaperType($paperType) {
        $this->paperType = $paperType;
        $this->paperSize = MgUtils::GetPaperSize($this->handler, $paperType);
        $this->printSize = new MgPlotSize($this->paperSize[0], $this->paperSize[1]);
    }

    public function SetOrientation($orientation) {
        $this->orientation = $orientation;
    }

    public function SetDisclaimer($disclaimer) {
        $this->disclaimer = $disclaimer;
    }

    public function SetTitle($title) {
        $this->title = $title;
    }

    public function SetSubTitle($subTitle) {
        $this->subTitle = $subTitle;
    }

    public function SetMargins($marginTIn, $marginBIn, $marginLIn, $marginRIn) {
        $this->margin = array(
            MgUtils::InToMM($marginTIn),
            MgUtils::InToMM($marginBIn),
            MgUtils::InToMM($marginLIn),
            MgUtils::InToMM($marginRIn));
    }

    public function SetLayered($bLayered) {
        $this->bLayered = $bLayered;
    }

    public function SetDebugging($bDebug) {
        $this->bDebug = $bDebug;
    }

    public function ShowLegend($bShow) {
        $this->showLegend = $bShow;
    }

    public function ShowDisclaimer($bShow) {
        $this->showDisclaimer = $bShow;
    }

    public function ShowCoordinates($bShow) {
        $this->showCoordinates = $bShow;
    }

    public function ShowScaleBar($bShow) {
        $this->showScaleBar = $bShow;
    }

    public function ShowNorthArrow($bShow) {
        $this->drawNorthArrow = $bShow;
    }

    private function DrawScale($ovScale = NULL) {
        $pageHeight = $this->pdf->getPageHeight();
        $paddingBottom = 7; $textPadding = 5; $fontSize = 6;
        $style = array("width" => 0.4, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(0, 0, 0));

        $start_x = MgUtils::ParseLocaleDouble($this->margin[2]);
        $start_y = $pageHeight - $paddingBottom;

        $lineMark_h = 3;
        $textMarkPadding = 1.0;

        //<editor-fold defaultstate="collapsed" desc="print the scale bar with meter">
        $unit = "m";
        $imageSpan = 20;            // the 20 is a suggested scale bar length
        $scale = $this->scaleDenominator;
        if ($ovScale != NULL)
            $scale = $ovScale;

        $realSpan = $scale * 0.02;  // $imageSpan / 1000

        if($realSpan >= 1000)
        {
            $unit = "km";
            $realSpan /= 1000;
            $realSpan = MgUtils::GetRoundNumber($realSpan);
            $imageSpan = ($realSpan * 1000000)/$scale;
        }
        else
        {
            $realSpan = MgUtils::GetRoundNumber($realSpan);
            $imageSpan = ($realSpan * 1000) / $scale;
        }

        $end_x = $start_x + $imageSpan;
        $end_y = $start_y;
        $meterTextMark = $realSpan." ".$unit;

        $this->pdf->SetFont($this->font, "", $fontSize, "", true);
        $this->pdf->Line($start_x, $start_y, $end_x, $end_y, $style);
        $this->pdf->Line($start_x, $start_y, $start_x, $start_y - $lineMark_h, $style);
        $this->pdf->Line($end_x, $end_y, $end_x, $end_y - $lineMark_h, $style);
        $fontSize = 7; $textHeight = 4;
        $this->pdf->SetFont($this->font, "", $fontSize, "", true);
        $this->pdf->Text($start_x + $textMarkPadding, $start_y - $textHeight, $meterTextMark);

        $textStart_x = $end_x;
        //</editor-fold>

        //<editor-fold defaultstate="collapsed" desc="print the scale bar with feet">
        $unit ="ft"; $aFeetPerMeter = 3.2808; $aFeetPerMile = 5280; $aMeterPerFeet = 0.3048;
        $imageSpan = 20; // the 20 is a suggested scale bar length, in "mm"
        $realSpan = ( ($scale * $imageSpan) / 1000 ) * $aFeetPerMeter;

        if($realSpan > $aFeetPerMile)
        {
            $unit = "mi";
            $realSpan /= $aFeetPerMile;
            $realSpan = MgUtils::GetRoundNumber($realSpan);
            $imageSpan = ( $realSpan * $aFeetPerMile * $aMeterPerFeet * 1000 ) / $scale;
        }
        else
        {
            $realSpan = MgUtils::GetRoundNumber($realSpan);
            $imageSpan = ( $realSpan * $aMeterPerFeet * 1000 ) / $scale;
        }

        $end_x = $start_x + $imageSpan;
        $end_y = $start_y;

        $feetTextMark = $realSpan.' '.$unit;

        $this->pdf->Line($start_x, $start_y, $end_x, $end_y, $style);
        $this->pdf->Line($start_x, $start_y, $start_x, $start_y + $lineMark_h, $style);
        $this->pdf->Line($end_x, $end_y, $end_x, $end_y + $lineMark_h, $style);

        $this->pdf->SetFont($this->font, "", $fontSize, "", true);
        $this->pdf->Text($start_x + $textMarkPadding, $start_y + 1, $feetTextMark);
        //</editor-fold>

        //determine where to begin to print the absolute scale and date info
        if($end_x > $textStart_x)
        {
            $textStart_x = $end_x;
        }

        $textStart_x += $textPadding;

        //write the scale
        $fontSize = 8;
        $this->pdf->SetFont($this->font, "", $fontSize, "", true);
        $scaleText = "Scale 1:".$scale;
        $this->pdf->Text($textStart_x, $end_y + 0.2, $scaleText);
        //write the date
        $date = date("M/d/Y");
        $this->pdf->Text($textStart_x + 0.3, $end_y - 3.8, $date);

        return new MgPdfPlotMetrics($this->margin[2],
                                    $start_y,
                                    $textStart_x,
                                    ($end_y + 0.2) - $start_y);
    }

    private function DrawDeclaration($offX, $legendWidthIn) {
        $declaration = $this->disclaimer;

        //$declaration_w = $this->pdf->GetStringWidth($declaration,$this->font,9);
        $this->pdf->SetFont($this->font, "", 9, "", true);

        $bottomPadding = 2.5;

        //Sometimes the declaration is too short, less than 100 unit, we could set the cell width as the string length
        //so it will align to the right
        $SingleLineDeclarationWidth  = $this->pdf->GetStringWidth($declaration, $this->font, "", 9, false) + $legendWidthIn;
        $tolerance = 3;
        $w = $this->pdf->getPageWidth() - $this->margin[0] - $this->margin[1] - $offX;

        if( $SingleLineDeclarationWidth + $tolerance < $w )
        {
            $w = $SingleLineDeclarationWidth + $tolerance;
        }

        $h = 5;
        $border = 0; //no border
        $align = "L";//align left
        $tolerance = 2;

        $x = MgUtils::ParseLocaleDouble($this->margin[2]) + max($offX, $legendWidthIn) + $tolerance;
        $cellTotalHeight = $this->pdf->getStringHeight($w,$declaration);
        $y = $this->pdf->getPageHeight() - $cellTotalHeight - $bottomPadding;
        if (strlen($declaration) == 0)
            return new MgPdfPlotMetrics($x, $y, 0, 0);

        $this->pdf->MultiCell($w,
                              $h,
                              $declaration,
                              $border,
                              $align,
                              false,
                              0,
                              $x,
                              $y,
                              true);
        return new MgPdfPlotMetrics($x, $y, $w, $this->pdf->getStringHeight($w, $declaration));
    }

    private function DrawExtentCS($legendWidthIn) {
        if ($this->normalizedBox && trim($this->normalizedBox) != "") {
            $fontSize = 9;
            $decimals = 6;
            $padding = 5;
            $textHeight = 5;

            $extent_cs = explode(",",$this->normalizedBox);//2,3 ; 6,7
            $lefttop_cs_label = " x:".number_format($extent_cs[6], $decimals).", y:".number_format($extent_cs[7], $decimals)."   ";
            $rightbottom_cs_label = " x:".number_format($extent_cs[2], $decimals).", y:".number_format($extent_cs[3], $decimals)."   ";
            $this->pdf->SetFont($this->font,
                                "",
                                $fontSize);

            //cell width
            $lt_cellwidth = $this->pdf->GetStringWidth($lefttop_cs_label, $this->font, '', $fontSize);
            $rb_cellwidth = $this->pdf->GetStringWidth($rightbottom_cs_label, $this->font, '', $fontSize);
            //cell location
            $lefttop = array((MgUtils::ParseLocaleDouble($this->margin[2]) + $padding),(MgUtils::ParseLocaleDouble($this->margin[0]) + $padding));
            $rightbottom = array((MgUtils::ParseLocaleDouble($this->margin[2]) + $this->printSize->width - $rb_cellwidth - $padding),(MgUtils::ParseLocaleDouble($this->margin[0]) + $this->printSize->height - $padding - $textHeight));

            $this->pdf->SetFillColor(255, 255, 255);

            $this->pdf->SetXY($lefttop[0] + $legendWidthIn, $lefttop[1], false);
            $this->pdf->Cell($lt_cellwidth,
                             0,
                             $lefttop_cs_label,
                             1,
                             0,
                             '',
                             true,
                             '',
                             0,
                             false,
                             self::CALIGN_CELL_TOP,
                             self::VALIGN_CENTER);

            $this->pdf->SetXY($rightbottom[0] + $legendWidthIn, $rightbottom[1], false);
            $this->pdf->Cell($rb_cellwidth,
                             0,
                             $rightbottom_cs_label,
                             1,
                             0,
                             '',
                             true,
                             '',
                             0,
                             false,
                             self::CALIGN_CELL_TOP,
                             self::VALIGN_CENTER);
        }
    }

    private function DrawTitle() {
        $html = '<div style="text-align:left"><span style="font-weight: bold; font-size: 18pt;">'.$this->title.'</span><br/><span>'.$this->subTitle.'</span></div>';

        //print title left position
        $titleWidth = $this->pdf->GetStringWidth($this->title, $this->font, "B", 18, false);
        $x = ($this->pdf->getPageWidth() - $titleWidth) / 2;
        if($x < 0.0)
        {
            $x = 0;
        }

        //print title top position
        $y = 5;
        if( $this->margin[0] > 0.0 )
        {
            $y = $this->margin[0] / 4;
        }

        // Print text using writeHTMLCell()
        $this->pdf->writeHTMLCell(0, 0, $x, $y, $html, 0, 1, false, true, "C", true);
        return new MgPdfPlotMetrics($x, $y, $titleWidth, $this->pdf->getStringHeight(0, $this->title));
    }

    private function DrawNorthArrow($imgMap) {
        // Load the north arrow image which has a 300 dpi resolution
        $na         = imagecreatefrompng(dirname(__FILE__)."/../res/north_arrow.png");

        $transparent= imagecolortransparent($na);
        // PHP 5.5 broke image rotation (or maybe we did it completely wrong before PHP 5.5).
        // Either way, here's how we fix it. Assign an explicit color if imagecolortransparent() returns -1
        if ($transparent < 0) {
            $transparent = imagecolorallocatealpha($na, 0, 0, 0, 127);
            $bReleaseTrans = true;
        }

        // Rotate the north arrow according to the capture rotation
        $rotatedNa  = imagerotate($na, -$this->rotation, $transparent);
        // Free the transparent color if we allocated it
        if ($bReleaseTrans)
            imagecolordeallocate($na, $transparent);

        // Free the north arrow image
        imagedestroy($na);
        // Get the size of north arrow image
        $naWidth    = imagesx($rotatedNa);
        $naHeight   = imagesy($rotatedNa);
        // Get the map size
        $imgMapWidth   = imagesx($imgMap);
        $imgMapHeight  = imagesy($imgMap);
        // Get the logical resolution of map
        $resolution = $imgMapWidth * 25.4 / $this->printSize->width;
        // On printed paper, north arrow is located at the right bottom corner with 6 MM margin
        $naRes      = 300;
        $naMargin   = 12;
        // Calculate the margin as pixels according to the resolutions
        $margin     = $resolution * $naMargin / 25.4;
        // Get the width of the north arrow on the map picture
        $drawWidth  = $naWidth * $resolution / $naRes;
        $drawHeight = $naHeight * $resolution / $naRes;
        // Draw the north arrow on the map picture
        imagecopyresized($imgMap, $rotatedNa, $imgMapWidth - $drawWidth - $margin, $imgMapHeight - $drawHeight - $margin, 0, 0, $drawWidth, $drawHeight, $naWidth, $naHeight);
        // Free the north arrow image
        imagedestroy($rotatedNa);
    }

    private static function CreatePolygon($coordinates) {
        $geometryFactory      = new MgGeometryFactory();
        $coordinateCollection = new MgCoordinateCollection();
        $linearRingCollection = new MgLinearRingCollection();

        for ($index = 0; $index < count($coordinates); ++$index)
        {
            $coordinate = $geometryFactory->CreateCoordinateXY(MgUtils::ParseLocaleDouble($coordinates[$index]), MgUtils::ParseLocaleDouble($coordinates[++$index]));
            $coordinateCollection->Add($coordinate);
        }

        $coordinateCollection->Add($geometryFactory->CreateCoordinateXY(MgUtils::ParseLocaleDouble($coordinates[0]), MgUtils::ParseLocaleDouble($coordinates[1])));

        $linearRingCollection = $geometryFactory->CreateLinearRing($coordinateCollection);
        $captureBox           = $geometryFactory->CreatePolygon($linearRingCollection, null);

        return $captureBox;
    }

    private static function GetTempPath() {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . "mgo" . uniqid();
    }

    private function RenderLegend($width, $height) {
        $bgColor = new MgColor(255, 255, 255);
        $mgReader = $this->renderingService->RenderMapLegend($this->map, $width, $height, $bgColor, MgImageFormats::Png);
        $tempImage = self::GetTempPath();
        $mgReader->ToFile($tempImage);
        return $tempImage;
    }

    private function RenderMap($width, $height, $ovCenter = NULL, $ovScale = NULL, $ovColor = NULL) {
        $size = new MgPlotSize($this->printSize->width / 25.4 * $this->dpi, $this->printSize->height / 25.4 * $this->dpi);
        $selection = new MgSelection($this->map);

        $captureBox = self::CreatePolygon(explode(",", $this->box));
        $normalizedCapture = self::CreatePolygon(explode(",", $this->normalizedBox));

        // Calculate the generated picture size
        $envelope         = $captureBox->Envelope();
        $normalizedE      = $normalizedCapture->Envelope();
        $size1            = new MgPlotSize($envelope->getWidth(), $envelope->getHeight());
        $size2            = new MgPlotSize($normalizedE->getWidth(), $normalizedE->getHeight());
        $toSize           = new MgPlotSize($size1->width / $size2->width * $size->width, $size1->height / $size2->height * $size->height);
        $centroid         = $captureBox->GetCentroid();

        if ($ovCenter != null)
            $center = $ovCenter;
        else
            $center = $centroid->GetCoordinate();

        if ($ovScale != null)
            $scale = $ovScale;
        else
            $scale = $this->scaleDenominator;

        $this->map->SetDisplayDpi($this->dpi);
        if ($ovColor != NULL) {
            $color = $ovColor;
        } else {
            $colorString = $this->map->GetBackgroundColor();
            // The returned color string is in AARRGGBB format. But the constructor of MgColor needs a string in RRGGBBAA format
            $colorString = substr($colorString, 2, 6) . substr($colorString, 0, 2);
            $color = new MgColor($colorString);
        }

        $mgReader = $this->renderingService->RenderMap($this->map,
                                                       $selection,
                                                       $center,
                                                       $scale,
                                                       $toSize->width,
                                                       $toSize->height,
                                                       $color,
                                                       MgImageFormats::Png,
                                                       true);
        $tempImage = self::GetTempPath();

        $mgReader->ToFile($tempImage);

        // Draw north arrow if specified
        if ($this->drawNorthArrow) {
            $imgMap = imagecreatefrompng($tempImage);
            $this->DrawNorthArrow($imgMap);
            imagepng($imgMap, $tempImage);
            imagedestroy($imgMap);
        }

        //TODO: Rotation support (not included from Fusion QuickPlot code). If included, we need to solve
        //the transparency issue when outputting a layered PDF

        return $tempImage;
    }

    public function Plot($center = NULL, $scale = NULL, $downloadFileName = NULL) {
        $legendWidthPx = 250;
        $legendWidthIn = 0;
        if ($this->showLegend) {
            $legendWidthIn = MgUtils::PxToIn($legendWidthPx, $this->legendDpi);
        }

        // Create new PDF document, the default "PDF_UNIT" value is "mm"
        $this->pdf = new TCPDF($this->orientation, PDF_UNIT, $this->paperType, true, "UTF-8", false);
        $this->font = "dejavusans";
        $this->pdf->AddFont($this->font);
        // Set margins
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->SetHeaderMargin(0);
        $this->pdf->SetFooterMargin(0);
        // Prevent adding page automatically
        $this->pdf->SetAutoPageBreak(false);

        // Remove default header/footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        // Set default font size
        $this->pdf->SetFont($this->font, "", 16);

        // Add a page
        $this->pdf->AddPage();

        // The print size determines the size of the PDF, not the size of the map and legend images
        // we want to request back to put into this PDF.
        //
        // What that means is that we should draw the surrounding print elements first (title, scale, disclaimer),
        // then adjust the image request sizes to ensure they (and element drawn on top like coordinates) will fit
        // correctly in the remaining space.
        //
        // Title, scale and disclaimer rendering will all return Metrics objects that will give us the information
        // needed for the size adjustments

        // Draw Title
        $mTitle = $this->DrawTitle();

        $mScale = NULL;
        if ($this->showScaleBar) {
            // Draw Scale
            $mScale = $this->DrawScale($scale);
        }

        // Draw declaration
        if ($this->showDisclaimer && strlen($this->disclaimer) > 0) {
            $offX = ($mScale != NULL) ? ($mScale->x + $mScale->w) : 0;
            $mDec = $this->DrawDeclaration($offX, $legendWidthIn);
        } else {
            $mDec = new MgPdfPlotMetrics(0, 0, 0, 0);
        }

        // Adjust width and height of the images we want to request to compensate for surrounding print elements that have been rendered

        // Check the size of the disclaimer and adjust height
        $idealHeight = $this->pdf->getPageHeight() - ($mDec->h - ($mScale != NULL ? $mScale->h : 0)) - $this->margin[0] - $this->margin[1];
        //var_dump($idealHeight);
        //var_dump($printSize);
        //die;
        if ($idealHeight < $this->printSize->height);
            $this->printSize->height = $idealHeight;

        $idealWidth = $this->pdf->getPageWidth() - $this->margin[2] - $this->margin[3];
        if ($this->showLegend) {
            $idealWidth -= $legendWidthIn;
        }
        if ($idealWidth < $this->printSize->width);
            $this->printSize->width = $idealWidth;


        $link = "";
        $align = "";
        $resize = false;
        $palign = "";
        $ismask = false;
        $imgmask = false;
        $border = 1;
        $fitbox = false;
        $hidden = false;
        $fitonpage = false;

        // Draw legend if specified
        if ($this->showLegend) {
            $legendfilelocation = $this->RenderLegend(MgUtils::InToPx($legendWidthIn, $this->legendDpi), MgUtils::InToPx($this->printSize->height, $this->legendDpi));
            $this->pdf->Image($legendfilelocation,
                              $this->margin[2],
                              $this->margin[0],
                              $legendWidthIn,
                              $this->printSize->height,
                              MgImageFormats::Png,
                              $link,
                              $align,
                              $resize,
                              $this->dpi,
                              $palign,
                              $ismask,
                              $imgmask,
                              $border,
                              $fitbox,
                              $hidden,
                              $fitonpage);
            @unlink($legendfilelocation);
        }

        if ($this->bLayered) {
            $layerNames = array();
            $tiledGroupNames = array();
            $mapLayers = $this->map->GetLayers();
            $mapGroups = $this->map->GetLayerGroups();
            //Collect all visible layers
            for ($i = $mapLayers->GetCount() - 1; $i >= 0; $i--) {
                $layer = $mapLayers->GetItem($i);
                if ($layer->IsVisible() && $layer->GetLayerType() == MgLayerType::Dynamic) {
                    array_push($layerNames, $layer->GetName());
                }
            }
            for ($i = $mapGroups->GetCount() - 1; $i >= 0; $i--) {
                $group = $mapGroups->GetItem($i);
                if ($group->IsVisible() && $group->GetLayerGroupType() != MgLayerGroupType::Normal) {
                    array_push($tiledGroupNames, $group->GetName());
                }
            }

            $bgColor = new MgColor("FFFFFF00");
            //Turn off all layers and tiled groups first
            for ($i = 0; $i < $mapGroups->GetCount(); $i++) {
                $group = $mapGroups->GetItem($i);
                if ($group->GetLayerGroupType() != MgLayerGroupType::Normal)
                    $group->SetVisible(false);
            }
            for ($i = 0; $i < $mapLayers->GetCount(); $i++) {
                $layer = $mapLayers->GetItem($i);
                if ($layer->GetLayerType() == MgLayerType::Dynamic)
                    $layer->SetVisible(false);
            }

            //Plot this map background
            $filelocation = $this->RenderMap(MgUtils::InToPx($this->printSize->width, $this->dpi),
                                                 MgUtils::InToPx($this->printSize->height, $this->dpi),
                                                 $center,
                                                 $scale);

            // Draw Map background
            $this->pdf->Image($filelocation,
                              ($this->showLegend ? ($this->margin[2] + $legendWidthIn) : $this->margin[2]),
                              $this->margin[0],
                              $this->printSize->width,
                              $this->printSize->height,
                              MgImageFormats::Png,
                              $link,
                              $align,
                              $resize,
                              $this->dpi,
                              $palign,
                              $ismask,
                              $imgmask,
                              $border,
                              $fitbox,
                              $hidden,
                              $fitonpage);
            @unlink($filelocation);

            $prevLayerName = NULL;
            $prevGroupName = NULL;
            //Plot each tiled group individually
            foreach ($tiledGroupNames as $groupName) {
                if ($prevGroupName != NULL) {
                    $mapGroups->GetItem($prevGroupName)->SetVisible(false);
                }
                $mapGroups->GetItem($groupName)->SetVisible(true);
                $print = true;
                $view = true;
                $lock = false;
                $this->pdf->startLayer($groupName,$print,$view,$lock);

                $filelocation = $this->RenderMap(MgUtils::InToPx($this->printSize->width, $this->dpi),
                                                 MgUtils::InToPx($this->printSize->height, $this->dpi),
                                                 $center,
                                                 $scale,
                                                 $bgColor);

                // Draw Map
                $this->pdf->Image($filelocation,
                                  ($this->showLegend ? ($this->margin[2] + $legendWidthIn) : $this->margin[2]),
                                  $this->margin[0],
                                  $this->printSize->width,
                                  $this->printSize->height,
                                  MgImageFormats::Png,
                                  $link,
                                  $align,
                                  $resize,
                                  $this->dpi,
                                  $palign,
                                  $ismask,
                                  $imgmask,
                                  $border,
                                  $fitbox,
                                  $hidden,
                                  $fitonpage);
                @unlink($filelocation);

                $prevGroupName = $groupName;
                $this->pdf->endLayer();
            }
            //Now plot each layer individually
            foreach ($layerNames as $layerName) {
                if ($prevLayerName != NULL) {
                    $mapLayers->GetItem($prevLayerName)->SetVisible(false);
                }
                $mapLayers->GetItem($layerName)->SetVisible(true);

                $print = true;
                $view = true;
                $lock = false;
                $this->pdf->startLayer($layerName,$print,$view,$lock);

                $filelocation = $this->RenderMap(MgUtils::InToPx($this->printSize->width, $this->dpi),
                                                 MgUtils::InToPx($this->printSize->height, $this->dpi),
                                                 $center,
                                                 $scale,
                                                 $bgColor);

                // Draw Map
                $this->pdf->Image($filelocation,
                                  ($this->showLegend ? ($this->margin[2] + $legendWidthIn) : $this->margin[2]),
                                  $this->margin[0],
                                  $this->printSize->width,
                                  $this->printSize->height,
                                  MgImageFormats::Png,
                                  $link,
                                  $align,
                                  $resize,
                                  $this->dpi,
                                  $palign,
                                  $ismask,
                                  $imgmask,
                                  $border,
                                  $fitbox,
                                  $hidden,
                                  $fitonpage);
                @unlink($filelocation);

                $prevLayerName = $layerName;
                $this->pdf->endLayer();
            }
        } else {
            $filelocation = $this->RenderMap(MgUtils::InToPx($this->printSize->width, $this->dpi),
                                             MgUtils::InToPx($this->printSize->height, $this->dpi),
                                             $center,
                                             $scale);

            // Draw Map
            $this->pdf->Image($filelocation,
                              ($this->showLegend ? ($this->margin[2] + $legendWidthIn) : $this->margin[2]),
                              $this->margin[0],
                              $this->printSize->width,
                              $this->printSize->height,
                              MgImageFormats::Png,
                              $link,
                              $align,
                              $resize,
                              $this->dpi,
                              $palign,
                              $ismask,
                              $imgmask,
                              $border,
                              $fitbox,
                              $hidden,
                              $fitonpage);
            @unlink($filelocation);
        }

        // Draw coordiates if specified
        $mExt = NULL;
        if ($this->showCoordinates) {
            // Draw Extent coordinates
            $mExt = $this->DrawExtentCS($legendWidthIn);
        }

        //NOTE: TCPDF will output the Content-Type header, but for some reason
        //chrome will refuse to display the PDF inline despite a Content-Type
        //header sent. Doing this will most likely double up the mime type, but
        //will ensure we can view pdfs inline in chrome. I've erred on letting
        //the mime type double up
        $this->handler->SetResponseHeader("Content-Type", "application/pdf");
        $mode = 'I';
        $name = 'Map.pdf';
        if (strlen($this->title) > 0)
            $name = $this->title.'.pdf';

        if ($downloadFileName != NULL) {
            $mode = 'D';
            $name = $downloadFileName;
        }

        // Close and output PDF document
        $this->pdf->Output($name, $mode);
    }
}