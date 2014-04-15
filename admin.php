<?php
require_once( dirname(__FILE__).'/form.lib.php' );

define( 'PHPFMG_USER', "litcanu@gmail.com" ); // must be a email address. for sending password to you.
define( 'PHPFMG_PW', "0cadc4" );

?>
<?php
/**
 * GNU Library or Lesser General Public License version 2.0 (LGPLv2)
*/

# main
# ------------------------------------------------------
error_reporting( E_ERROR ) ;
phpfmg_admin_main();
# ------------------------------------------------------




function phpfmg_admin_main(){
    $mod  = isset($_REQUEST['mod'])  ? $_REQUEST['mod']  : '';
    $func = isset($_REQUEST['func']) ? $_REQUEST['func'] : '';
    $function = "phpfmg_{$mod}_{$func}";
    if( !function_exists($function) ){
        phpfmg_admin_default();
        exit;
    };

    // no login required modules
    $public_modules   = false !== strpos('|captcha|', "|{$mod}|", "|ajax|");
    $public_functions = false !== strpos('|phpfmg_ajax_submit||phpfmg_mail_request_password||phpfmg_filman_download||phpfmg_image_processing||phpfmg_dd_lookup|', "|{$function}|") ;   
    if( $public_modules || $public_functions ) { 
        $function();
        exit;
    };
    
    return phpfmg_user_isLogin() ? $function() : phpfmg_admin_default();
}

function phpfmg_ajax_submit(){
    $phpfmg_send = phpfmg_sendmail( $GLOBALS['form_mail'] );
    $isHideForm  = isset($phpfmg_send['isHideForm']) ? $phpfmg_send['isHideForm'] : false;

    $response = array(
        'ok' => $isHideForm,
        'error_fields' => isset($phpfmg_send['error']) ? $phpfmg_send['error']['fields'] : '',
        'OneEntry' => isset($GLOBALS['OneEntry']) ? $GLOBALS['OneEntry'] : '',
    );
    
    @header("Content-Type:text/html; charset=$charset");
    echo "<html><body><script>
    var response = " . json_encode( $response ) . ";
    try{
        parent.fmgHandler.onResponse( response );
    }catch(E){};
    \n\n";
    echo "\n\n</script></body></html>";

}


function phpfmg_admin_default(){
    if( phpfmg_user_login() ){
        phpfmg_admin_panel();
    };
}



function phpfmg_admin_panel()
{    
    phpfmg_admin_header();
    phpfmg_writable_check();
?>    
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign=top style="padding-left:280px;">

<style type="text/css">
    .fmg_title{
        font-size: 16px;
        font-weight: bold;
        padding: 10px;
    }
    
    .fmg_sep{
        width:32px;
    }
    
    .fmg_text{
        line-height: 150%;
        vertical-align: top;
        padding-left:28px;
    }

</style>

<script type="text/javascript">
    function deleteAll(n){
        if( confirm("Are you sure you want to delete?" ) ){
            location.href = "admin.php?mod=log&func=delete&file=" + n ;
        };
        return false ;
    }
</script>


<div class="fmg_title">
    1. Email Traffics
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=1">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=1">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_EMAILS_LOGFILE) ){
            echo '<a href="#" onclick="return deleteAll(1);">delete all</a>';
        };
    ?>
</div>


<div class="fmg_title">
    2. Form Data
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=2">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=2">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_SAVE_FILE) ){
            echo '<a href="#" onclick="return deleteAll(2);">delete all</a>';
        };
    ?>
</div>
		</td>
	</tr>
</table>

<?php
    phpfmg_admin_footer();
}



function phpfmg_admin_header( $title = '' ){
    header( "Content-Type: text/html; charset=" . PHPFMG_CHARSET );
?>
<html>
<head>
    <title><?php echo '' == $title ? '' : $title . ' | ' ; ?>Oak Appartments Admin Panel </title>
    <meta name="keywords" content="">
    <meta name="description" content=" ">
    <meta name="generator" content="">

    <style type='text/css'>
    body, td, label, div, span{
        font-family : Verdana, Arial, Helvetica, sans-serif;
        font-size : 12px;
    }
    </style>
</head>
<body  marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">

<table cellspacing=0 cellpadding=0 border=0 width="100%">
    <td nowrap align=center style="background-color:#024e7b;padding:10px;font-size:18px;color:#ffffff;font-weight:bold;width:250px;" >
        Form Admin Panel
    </td>
    <td style="padding-left:30px;background-color:#86BC1B;width:100%;font-weight:bold;" >
        &nbsp;
<?php
    if( phpfmg_user_isLogin() ){
        echo '<a href="admin.php" style="color:#ffffff;">Main Menu</a> &nbsp;&nbsp;' ;
        echo '<a href="admin.php?mod=user&func=logout" style="color:#ffffff;">Logout</a>' ;
    }; 
?>
    </td>
</table>

<div style="padding-top:28px;">

<?php
    
}


function phpfmg_admin_footer(){
?>

</div>

<div style="color:#cccccc;text-decoration:none;padding:18px;font-weight:bold;">
	:: <a href="http://phpfmg.sourceforge.net" target="_blank" title="Free Mailform Maker: Create read-to-use Web Forms in a flash. Including validating form with CAPTCHA security image, send html email with attachments, send auto response email copy, log email traffics, save and download form data in Excel. " style="display:none;color:#cccccc;font-weight:bold;text-decoration:none;">PHP FormMail Generator</a> ::
</div>

</body>
</html>
<?php
}


function phpfmg_image_processing(){
    $img = new phpfmgImage();
    $img->out_processing_gif();
}


# phpfmg module : captcha
# ------------------------------------------------------
function phpfmg_captcha_get(){
    $img = new phpfmgImage();
    $img->out();
    //$_SESSION[PHPFMG_ID.'fmgCaptchCode'] = $img->text ;
    $_SESSION[ phpfmg_captcha_name() ] = $img->text ;
}



function phpfmg_captcha_generate_images(){
    for( $i = 0; $i < 50; $i ++ ){
        $file = "$i.png";
        $img = new phpfmgImage();
        $img->out($file);
        $data = base64_encode( file_get_contents($file) );
        echo "'{$img->text}' => '{$data}',\n" ;
        unlink( $file );
    };
}


function phpfmg_dd_lookup(){
    $paraOk = ( isset($_REQUEST['n']) && isset($_REQUEST['lookup']) && isset($_REQUEST['field_name']) );
    if( !$paraOk )
        return;
        
    $base64 = phpfmg_dependent_dropdown_data();
    $data = @unserialize( base64_decode($base64) );
    if( !is_array($data) ){
        return ;
    };
    
    
    foreach( $data as $field ){
        if( $field['name'] == $_REQUEST['field_name'] ){
            $nColumn = intval($_REQUEST['n']);
            $lookup  = $_REQUEST['lookup']; // $lookup is an array
            $dd      = new DependantDropdown(); 
            echo $dd->lookupFieldColumn( $field, $nColumn, $lookup );
            return;
        };
    };
    
    return;
}


function phpfmg_filman_download(){
    if( !isset($_REQUEST['filelink']) )
        return ;
        
    $info =  @unserialize(base64_decode($_REQUEST['filelink']));
    if( !isset($info['recordID']) ){
        return ;
    };
    
    $file = PHPFMG_SAVE_ATTACHMENTS_DIR . $info['recordID'] . '-' . $info['filename'];
    phpfmg_util_download( $file, $info['filename'] );
}


class phpfmgDataManager
{
    var $dataFile = '';
    var $columns = '';
    var $records = '';
    
    function phpfmgDataManager(){
        $this->dataFile = PHPFMG_SAVE_FILE; 
    }
    
    function parseFile(){
        $fp = @fopen($this->dataFile, 'rb');
        if( !$fp ) return false;
        
        $i = 0 ;
        $phpExitLine = 1; // first line is php code
        $colsLine = 2 ; // second line is column headers
        $this->columns = array();
        $this->records = array();
        $sep = chr(0x09);
        while( !feof($fp) ) { 
            $line = fgets($fp);
            $line = trim($line);
            if( empty($line) ) continue;
            $line = $this->line2display($line);
            $i ++ ;
            switch( $i ){
                case $phpExitLine:
                    continue;
                    break;
                case $colsLine :
                    $this->columns = explode($sep,$line);
                    break;
                default:
                    $this->records[] = explode( $sep, phpfmg_data2record( $line, false ) );
            };
        }; 
        fclose ($fp);
    }
    
    function displayRecords(){
        $this->parseFile();
        echo "<table border=1 style='width=95%;border-collapse: collapse;border-color:#cccccc;' >";
        echo "<tr><td>&nbsp;</td><td><b>" . join( "</b></td><td>&nbsp;<b>", $this->columns ) . "</b></td></tr>\n";
        $i = 1;
        foreach( $this->records as $r ){
            echo "<tr><td align=right>{$i}&nbsp;</td><td>" . join( "</td><td>&nbsp;", $r ) . "</td></tr>\n";
            $i++;
        };
        echo "</table>\n";
    }
    
    function line2display( $line ){
        $line = str_replace( array('"' . chr(0x09) . '"', '""'),  array(chr(0x09),'"'),  $line );
        $line = substr( $line, 1, -1 ); // chop first " and last "
        return $line;
    }
    
}
# end of class



# ------------------------------------------------------
class phpfmgImage
{
    var $im = null;
    var $width = 73 ;
    var $height = 33 ;
    var $text = '' ; 
    var $line_distance = 8;
    var $text_len = 4 ;

    function phpfmgImage( $text = '', $len = 4 ){
        $this->text_len = $len ;
        $this->text = '' == $text ? $this->uniqid( $this->text_len ) : $text ;
        $this->text = strtoupper( substr( $this->text, 0, $this->text_len ) );
    }
    
    function create(){
        $this->im = imagecreate( $this->width, $this->height );
        $bgcolor   = imagecolorallocate($this->im, 255, 255, 255);
        $textcolor = imagecolorallocate($this->im, 0, 0, 0);
        $this->drawLines();
        imagestring($this->im, 5, 20, 9, $this->text, $textcolor);
    }
    
    function drawLines(){
        $linecolor = imagecolorallocate($this->im, 210, 210, 210);
    
        //vertical lines
        for($x = 0; $x < $this->width; $x += $this->line_distance) {
          imageline($this->im, $x, 0, $x, $this->height, $linecolor);
        };
    
        //horizontal lines
        for($y = 0; $y < $this->height; $y += $this->line_distance) {
          imageline($this->im, 0, $y, $this->width, $y, $linecolor);
        };
    }
    
    function out( $filename = '' ){
        if( function_exists('imageline') ){
            $this->create();
            if( '' == $filename ) header("Content-type: image/png");
            ( '' == $filename ) ? imagepng( $this->im ) : imagepng( $this->im, $filename );
            imagedestroy( $this->im ); 
        }else{
            $this->out_predefined_image(); 
        };
    }

    function uniqid( $len = 0 ){
        $md5 = md5( uniqid(rand()) );
        return $len > 0 ? substr($md5,0,$len) : $md5 ;
    }
    
    function out_predefined_image(){
        header("Content-type: image/png");
        $data = $this->getImage(); 
        echo base64_decode($data);
    }
    
    // Use predefined captcha random images if web server doens't have GD graphics library installed  
    function getImage(){
        $images = array(
			'92F5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDA0MDkMREprC2sjYwOiCrC2gVaXTFEGMAibk6ILlv2tRVS5eGroyKQnIfqyvDFFaQucg2tzIEoIsJtDI6gOxFFgO6pQGoLgDZfawBoqGuDQxTHQZB+FERYnEfAOyWyl/n9FW0AAAAAElFTkSuQmCC',
			'D2D5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nM2QMQ6AIAxFYegN8D51YK+JHeQ0ZeAGeAQWTiluRRw1oX97+U1fauowYmbKL35Mdge2TIpRhgRxRd2j5KKX7cHMzTwqv1BqKfUIQfm1XgYhcf0ujcwitBuudxGISNqPaWHP5sQJ/vdhXvwuUGvOArCeF3YAAAAASUVORK5CYII=',
			'B961' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7QgMYQxhCGVqRxQKmsLYyOjpMRRFrFWl0bXAIRVUHEoPrBTspNGrp0tSpq5Yiuy9gCmOgq6MDqh2tDEC9AWhiLJhiELegiEHdHBowCMKPihCL+wA+NM3l5gjO/wAAAABJRU5ErkJggg==',
			'3175' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7RAMYAlhDA0MDkMQCpjAGMDQEOqCobGXFFJvCEMDQ6OjqgOS+lVGrolYtXRkVhew+kLopDA0iKOYBxQIwxRgdGB1EUNwCdF8DQwCy+0SBLgaKTXUYBOFHRYjFfQBLockBv0ST9QAAAABJRU5ErkJggg==',
			'5AF3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMYAlhDA0IdkMQCGhhDWBsYHQJQxFhbWYG0CJJYYIBIoytYDuG+sGnTVqaGrlqahey+VhR1UDHRUFc08wKg6pDFRKaAxFDdwgqxF8XNAxV+VIRY3AcAQ2TNK4A9PbsAAAAASUVORK5CYII=',
			'252F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7WANEQxlCGUNDkMREpog0MDo6OiCrC2gVaWBtCEQRY2gVCWFAiEHcNG3q0lUrM0OzkN0XwNDo0MqIohfIa3SYgirG2iDS6BCAKga0FagTVSw0lDGENRTNLQMUflSEWNwHAO2WyJ0FLRE5AAAAAElFTkSuQmCC',
			'F3A3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7QkNZQximMIQ6IIkFNIi0MoQyOgSgiDE0Ojo6NIigirWyAskAJPeFRq0KW7oqamkWkvvQ1MHNcw0NQDev0bUBXUwEqDcQzS2sIUDzUNw8UOFHRYjFfQBDrM7znKgDlgAAAABJRU5ErkJggg==',
			'6161' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WAMYAhhCGVqRxUSmMAYwOjpMRRYLaGENYG1wCEURa2AAisH1gp0UGbUqaunUVUuR3RcyBajO0QHFjoBWkN4AgmIiQL2MaHqBLgkFujk0YBCEHxUhFvcBAHHtyhp82itSAAAAAElFTkSuQmCC',
			'0ED5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7GB1EQ1lDGUMDkMRYA0QaWBsdHZDViUwBijUEoogFtILFXB2Q3Be1dGrY0lWRUVFI7oOoC2gQwdCLKgazQwTDLQ4ByO6DuJlhqsMgCD8qQizuAwAG3ct1q+fkVAAAAABJRU5ErkJggg==',
			'3BB3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWUlEQVR4nGNYhQEaGAYTpIn7RANEQ1hDGUIdkMQCpoi0sjY6OgQgq2wVaXRtCGgQQRYDq3NoCEBy38qoqWFLQ1ctzUJ2H6o63OZhEcPmFmxuHqjwoyLE4j4AOU/N3YmFFCkAAAAASUVORK5CYII=',
			'1B21' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7GB1EQxhCGVqRxVgdRFoZHR2mIouJOog0ujYEhKLqFQHqC4DpBTtpZdbUMCCxFNl9YHWtqHYAxRodpmARC8AQa2V0QBUTDRENYQ0NCA0YBOFHRYjFfQA3T8kQqJJqLwAAAABJRU5ErkJggg==',
			'E363' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXElEQVR4nGNYhQEaGAYTpIn7QkNYQxhCGUIdkMQCGkRaGR0dHQJQxBgaXRscGkRQxVpZITTcfaFRq8KWTl21NAvJfWB1jg4NmOYFoJuHRQzTLdjcPFDhR0WIxX0AS7fN8K3xmCkAAAAASUVORK5CYII=',
			'6D56' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7WANEQ1hDHaY6IImJTBFpZW1gCAhAEgtoEWl0bWB0EEAWawCKTWV0QHZfZNS0lamZmalZSO4LmSLS6NAQiGpeK1jMQQRNzBVNDOQWRkcHFL0gNzOEMqC4eaDCj4oQi/sADsTM8fLtbkkAAAAASUVORK5CYII=',
			'51F5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMYAlhDA0MDkMQCGhgDWBsYHRhQxFgxxAIDGEBirg5I7gubtipqaejKqChk97WC1DE0iCDbjEUsACzG6IAsJjIFrC4A2X1Al4QCxaY6DILwoyLE4j4AypXIqj6PHiYAAAAASUVORK5CYII=',
			'0586' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7GB1EQxlCGaY6IImxBog0MDo6BAQgiYlMEWlgbQh0EEASC2gVCWF0dHRAdl/U0qlLV4WuTM1Ccl9AK0Ojo6MjinkgMVegeSKodmCIsQawtqK7hdGBMQTdzQMVflSEWNwHALTByx6oORkiAAAAAElFTkSuQmCC',
			'2208' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpIn7WAMYQximMEx1QBITmcLayhDKEBCAJBbQKtLo6OjoIIKsu5Wh0bUhAKYO4qZpq5YuXRU1NQvZfQEMU1gR6sCQ0YEhgLUhEMU8VqAoI5odIkBRdLeEhoqGOqC5eaDCj4oQi/sAVsfLdEwM6/sAAAAASUVORK5CYII=',
			'4790' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpI37poiGOoQytKKIhTA0Ojo6THVAEmMEirk2BAQEIImxTmFoZW0IdBBBct+0aaumrcyMzJqG5L6AKQwBDCFwdWAYGsrowNCAKsYwhbWBEc0OhikiDYxobgGJMaC7eaDCj3oQi/sAqiLLwfyuPa0AAAAASUVORK5CYII=',
			'9C95' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7WAMYQxlCGUMDkMREprA2Ojo6OiCrC2gVaXBtCMQQY20IdHVAct+0qdNWrcyMjIpCch+rq0gDQ0hAgwiyza0gHqqYAFDMEWgHshjELQ4ByO6DuJlhqsMgCD8qQizuAwB6vsu2qx7aAgAAAABJRU5ErkJggg==',
			'705C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nM2QoQ2AQAxF24RucAMVgS8JNXjEMUVPsAGwA0wJuGsOCYF+98x7KezFGfxpr/SpgpDyIjmdsCMDCY7RRIZc5WwOqVmQXV+/bkOMY96HHBJby7mXrGTBLkfrHGLYYc2u5WwVUPDNH/3vwd30HcqOymFrCJiQAAAAAElFTkSuQmCC',
			'C359' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WEOAMNRhqgOSmEirSCtrA0NAAJJYQCNDo2sDo4MIslgDQyvrVLgY2ElRq1aFLc3MigpDch9IHZCciqa30QFIimDYEYBiB8gtjI4OKG4BuZkhlAHFzQMVflSEWNwHADu6zE2YkftIAAAAAElFTkSuQmCC',
			'10A2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpIn7GB0YAhimMEx1QBJjdWAMYQhlCAhAEhN1YG1ldHR0EEHRK9Lo2hDQIILkvpVZ01amrooCQoT7oOoaHdD1hga0orqFtZW1IWAKqhhjCFAsAFlMNIQhgLUhMDRkEIQfFSEW9wEAA/nJ4F8PbZ8AAAAASUVORK5CYII=',
			'0E22' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB1EQxlCGaY6IImxBog0MDo6BAQgiYlMEWlgbQh0EEESC2gF8QIaRJDcF7V0atiqlVmropDcB1bXytDogK53ClAUzQ6GAKAoulscgKJobmYNDQwNGQThR0WIxX0AoKDK03oOupYAAAAASUVORK5CYII=',
			'866A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WAMYQxhCGVqRxUSmsLYyOjpMdUASC2gVaWRtcAgIQFEn0sDawOggguS+pVHTwpZOXZk1Dcl9IlNEW1kdHWHq4Oa5NgSGhmCKoaiDuAVVL8TNjChiAxV+VIRY3AcAaZzLghgacEUAAAAASUVORK5CYII=',
			'C4A5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7WEMYWhmmMIYGIImJtDJMZQhldEBWF9AIFHF0RBVrYHRlbQh0dUByX9SqpUuXroqMikJyXwDQRFYQiaJXNNQ1FE2skQGoLtBBBNUtIL0ByO4DuRkoNtVhEIQfFSEW9wEAybzMUCngs1QAAAAASUVORK5CYII=',
			'741F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QkMZWhmmMIaGIIu2MkxlCGF0YEAVC2VEF5vC6ArEMDGIm6KWLl01bWVoFpL7GB1EWpHUgSFrg2ioA5qYSAMDhroAHGKMoY6obhmg8KMixOI+AGLqyHORkrJcAAAAAElFTkSuQmCC',
			'3AFD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7RAMYAlhDA0MdkMQCpjCGsDYwOgQgq2xlbQWJiSCLTRFpdEWIgZ20MmraytTQlVnTkN2Hqg5qnmgophimugCoXmS3iAaAxVDcPFDhR0WIxX0AarrLBKX0pDYAAAAASUVORK5CYII=',
			'9F1C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WANEQx2mMEwNQBITmSLSwBDCECCCJBbQKtLAGMLowIImxjCF0QHZfdOmTg1bNW1lFrL7WF1R1EFgK6aYAFQM2Q6wW6aguoUVyGMMdUBx80CFHxUhFvcBANNaykZIe0l6AAAAAElFTkSuQmCC',
			'676D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7WANEQx1CGUMdkMREpjA0Ojo6OgQgiQW0MDS6Njg6iCCLNTC0sjYwwsTAToqMWjVt6dSVWdOQ3BcyhSGA1RFNbyujA2tDIJoYawO6mMgUkQZGNLewBgBVoLl5oMKPihCL+wBHXstfjj5peAAAAABJRU5ErkJggg==',
			'88EF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAUElEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDHUNDkMREprC2sjYwOiCrC2gVaXRFE0NTB3bS0qiVYUtDV4ZmIbmPWPOIsAPZzShiAxV+VIRY3AcAsirJTtbQ6xQAAAAASUVORK5CYII=',
			'8EEC' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAVklEQVR4nGNYhQEaGAYTpIn7WANEQ1lDHaYGIImJTBFpYG1gCBBBEgtoBYkxOrBgqGN0QHbf0qipYUtDV2Yhuw9NHYp52MQw7UB1CzY3D1T4URFicR8Az8TKS1MrJUcAAAAASUVORK5CYII=',
			'5975' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdklEQVR4nM3QsRGAMAhAUXInG2QgLOzJnbFwBKcgBRuoG6TQKU1J1FLvAt1veAecjxFoaX/xxdGNGENk01hQQQJB1XyiWwtcWuoHMr5pz3nJxzxbn7pAK4i3lxUScd1Yu9STI9v8iooCbH3IxSywUQP/+3BffBdMiMwNvpmE1QAAAABJRU5ErkJggg==',
			'1170' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7GB0YAlhDA1qRxVgdGAMYGgKmOiCJiTqwgsQCAtD0MjQ6OogguW9l1qqoVUtXZk1Dch9Y3RRGmDqEWACmGAij28HawIDqlhDWUKAYipsHKvyoCLG4DwDZ+sbwZ8PhLwAAAABJRU5ErkJggg==',
			'6A4E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7WAMYAhgaHUMDkMREpjCGMLQ6OiCrC2hhbWWYiibWINLoEAgXAzspMmrayszMzNAsJPeFTBFpdG1E09sqGuoaGogmBjQPTZ3IFEwx1gCwGIqbByr8qAixuA8AsTfMG4iSTJkAAAAASUVORK5CYII=',
			'82A7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdUlEQVR4nGNYhQEaGAYTpIn7WAMYQximMIaGIImJTGFtZQgF0khiAa0ijY6ODihiIlMYGl0bAoAQ4b6lUauWLl0VtTILyX1AdVNYgSYwoJjHEMAaGjAFVYzRAagugAHVLQ2sDYEOqG4WDXVFExuo8KMixOI+AOIbzLJcRAQEAAAAAElFTkSuQmCC',
			'2BD3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7WANEQ1hDGUIdkMREpoi0sjY6OgQgiQW0ijS6NgQ0iCDrbgWqA4oFILtv2tSwpauilmYhuy8ARR0YMjpgmsfagCkm0oDpltBQTDcPVPhREWJxHwDujc2f0AsCuwAAAABJRU5ErkJggg==',
			'D7D1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QgNEQ11DGVqRxQKmMDS6NjpMRRFrBYo1BISiibWygkgk90UtXTVtKYhEch9QRQCSOqgYowOmGGsDhtgUkQbWRgcUsdAAoFgoQ2jAIAg/KkIs7gMAggDOwnWPyGIAAAAASUVORK5CYII=',
			'569A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdklEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGVqRxQIaWFsZHR2mOqCIiTSyNgQEBCCJBQaINLA2BDqIILkvbNq0sJWZkVnTkN3XKtrKEAJXBxUTaXRoCAwNQbYDKObYgKpOZArILY4oYqwBIDczopo3QOFHRYjFfQCCt8tpA8pB+QAAAABJRU5ErkJggg==',
			'E39C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7QkNYQxhCGaYGIIkFNIi0Mjo6BIigiDE0ujYEOrCgirWyAsWQ3RcatSpsZWZkFrL7QOoYQuDq4OY5NGCKOWLYgekWbG4eqPCjIsTiPgAeZ8woP1z2fAAAAABJRU5ErkJggg==',
			'FE0A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7QkNFQxmmMLQiiwU0iDQwhDJMdUATY3R0CAhAE2NtCHQQQXJfaNTUsKWrIrOmIbkPTR2yWGgIhh2OGOoYQhnRxEBuRhUbqPCjIsTiPgBie8w5WcqiWAAAAABJRU5ErkJggg==',
			'4494' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpI37pjC0MoQyNAQgi4UwTGV0dGhEFmMMYQhlbQhoRRZjncLoChSbEoDkvmnTli5dmRkVFYXkvoApIq0MIYEOyHpDQ0VDHRoCQ0PQ3MIIdEkAupijA4YYhpsHKvyoB7G4DwAu0c0qFyTp9wAAAABJRU5ErkJggg==',
			'96C3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7WAMYQxhCHUIdkMREprC2MjoEOgQgiQW0ijSyNgg0iKCKNbCCaCT3TZs6LWzpqlVLs5Dcx+oq2oqkDgKB5rmC7EISEwCLodqBzS3Y3DxQ4UdFiMV9AFVMzEo+Ss1/AAAAAElFTkSuQmCC',
			'B65C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7QgMYQ1hDHaYGIIkFTGFtZW1gCBBBFmsVaWRtYHRgQVEn0sA6ldEB2X2hUdPClmZmZiG7L2CKaCtDQ6ADA5p5DljEXIFiqHawtjI6OqC4BeRmhlAGFDcPVPhREWJxHwBWqcxeoDqXVgAAAABJRU5ErkJggg==',
			'AE9B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7GB1EQxlCGUMdkMRYA0QaGB0dHQKQxESmiDSwNgQ6iCCJBbRCxAKQ3Be1dGrYyszI0Cwk94HUMYQEopgXGgoyCdM8RmxiaG4JaMV080CFHxUhFvcBAMocy1yWr7CYAAAAAElFTkSuQmCC',
			'0AD1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7GB0YAlhDGVqRxVgDGENYGx2mIouJTGFtZW0ICEUWC2gVaXQFksjui1o6bWUqkER2H5o6qJhoKLqYyBRMdawBQLFGBxQxRgegWChDaMAgCD8qQizuAwAlks1UMHIflAAAAABJRU5ErkJggg==',
			'9A1D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WAMYAhimMIY6IImJTGEMYQhhdAhAEgtoZW1lBIqJoIiJNDpMgYuBnTRt6rSVWSCE5D5WVxR1ENgqGoouJtCKqU5kCkQM2S2sASKNjqGOKG4eqPCjIsTiPgBv7MsWR3EZYQAAAABJRU5ErkJggg==',
			'57DB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7QkNEQ11DGUMdkMQCGhgaXRsdHQLQxRoCHUSQxAIDGFpZgWIBSO4Lm7Zq2tJVkaFZyO5rZQhAUgcVY3RgRTMvAGgaupjIFJEGVjS3sAYAxdDcPFDhR0WIxX0AzqPMa3sWTrwAAAAASUVORK5CYII=',
			'2D5A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAd0lEQVR4nGNYhQEaGAYTpIn7WANEQ1hDHVqRxUSmiLSyNjBMdUASC2gVaXRtYAgIQNYNEpvK6CCC7L5p01amZmZmTUN2X4BIo0NDIEwdGAJ1gcRCQ5Dd0gCyA1WdSINIK6OjI4pYaKhoCEMoI4rYQIUfFSEW9wEAxuvLsOBfyfcAAAAASUVORK5CYII=',
			'5504' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7QkNEQxmmMDQEIIkFNIg0MIQyNKKLMTo6tCKLBQaIhLA2BEwJQHJf2LSpS5euioqKQnZfK0Oja0OgA7JeqFhoCLIdrSKNjo4OKG4RmcLaCnQLihhrAGMIupsHKvyoCLG4DwCvM84ew+u5agAAAABJRU5ErkJggg==',
			'A821' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7GB0YQxhCGVqRxVgDWFsZHR2mIouJTBFpdG0ICEUWC2hlbQWRyO6LWroybNXKrKXI7gOra0W1IzRUpNFhCqpYQCtQLABdDOgWB3QxxhDW0IDQgEEQflSEWNwHANcHzDS7v6VRAAAAAElFTkSuQmCC',
			'4E40' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpI37poiGMjQ6tKKIhYg0MLQ6THVAEmMEiU11CAhAEmOdAhQLdHQQQXLftGlTw1ZmZmZNQ3JfAFAdayNcHRiGhgLFQgNRxBhA5jWi2gEVQ3ELVjcPVPhRD2JxHwBVUsxvmp7u6QAAAABJRU5ErkJggg==',
			'6D8E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7WANEQxhCGUMDkMREpoi0Mjo6OiCrC2gRaXRtCEQVaxBpdESoAzspMmrayqzQlaFZSO4LmYKiDqK3FYt5WMSwuQWbmwcq/KgIsbgPAKuxywXtOCIHAAAAAElFTkSuQmCC',
			'0976' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeElEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDA6Y6IImxBrC2MjQEBAQgiYlMEWl0aAh0EEASC2gFijU6OiC7L2rp0qVZS1emZiG5L6CVMdBhCiOKeQGtDI0OAYwOIih2sABNQxUDuYW1gQFFL9jNDQwobh6o8KMixOI+ALV4y5fvcJLFAAAAAElFTkSuQmCC',
			'FF22' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QkNFQx1CGaY6IIkFNIg0MDo6BASgibE2BDqIoInBSJj7QqOmhq1ambUqCsl9YBWtDI3odjBMAYqiiwUARdHd4gAURXdLaGBoyCAIPypCLO4DAHrxzRwMisVFAAAAAElFTkSuQmCC',
			'59F3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7QkMYQ1hDA0IdkMQCGlhbWRsYHQJQxEQaXYG0CJJYYABELADJfWHTli5NDV21NAvZfa2MgUjqoGIMGOYFtLJgiIlMwXQLawDQzQ0MKG4eqPCjIsTiPgASGMy0w5P4tQAAAABJRU5ErkJggg==',
			'6365' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WANYQxhCGUMDkMREpoi0Mjo6OiCrC2hhaHRtQBNrYGhlbWB0dUByX2TUqrClU1dGRSG5L2QKUJ2jQ4MIst5WkHkBWMQCHUQw3OIQgOw+iJsZpjoMgvCjIsTiPgBUisu+W6Lj0gAAAABJRU5ErkJggg==',
			'E703' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7QkNEQx2mMIQ6IIkFNDA0OoQyOgSgiTk6OjSIoIq1sgLJACT3hUatmrZ0VdTSLCT3AeUDkNRBxRgdQGKo5rE2MGLYAeShuSU0BCiG5uaBCj8qQizuAwByus3g6trXpQAAAABJRU5ErkJggg==',
			'19DD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDGUMdkMRYHVhbWRsdHQKQxEQdRBpdGwIdRFD0ooiBnbQya+nS1FWRWdOQ3Ae0IxBTLwMW81iwiGFxSwimmwcq/KgIsbgPAL27yXkLXu/TAAAAAElFTkSuQmCC',
			'BCF9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7QgMYQ1lDA6Y6IIkFTGFtdG1gCAhAFmsVaXBtYHQQQVEn0sCKEAM7KTRq2qqloauiwpDcB1HHMFUEzTygWAO6GNBeNDsw3QJ2M9A8ZDcPVPhREWJxHwDPfM16FkrozAAAAABJRU5ErkJggg==',
			'CADF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7WEMYAlhDGUNDkMREWhlDWBsdHZDVBTSytrI2BKKKNYg0uiLEwE6KWjVtZeqqyNAsJPehqYOKiYZiiDViqhNpBYqhuYU1BCgWyogiNlDhR0WIxX0AdIbLyEAhfc8AAAAASUVORK5CYII=',
			'277A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WANEQ11DA1qRxUSmMDQ6NARMdUASA6oAiQUEIOtuBcJGRwcRZPdNA8KlK7OmIbsvAAinMMLUgSGjA6MDQwBjaAiyW4AQJI6sTgQIQaLIYqGhmGIDFX5UhFjcBwBFAcrkm5c/sgAAAABJRU5ErkJggg==',
			'8063' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7WAMYAhhCGUIdkMREpjCGMDo6OgQgiQW0srayNjg0iKCoE2l0BckhuW9p1LSVqVNXLc1Cch9YnaNDA6p5IL0BKOZB7AhAswPTLdjcPFDhR0WIxX0AvVPMsqoFSGoAAAAASUVORK5CYII=',
			'8D6A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7WANEQxhCGVqRxUSmiLQyOjpMdUASC2gVaXRtcAgIQFUHFGN0EEFy39KoaStTp67MmobkPrA6R0eYOiTzAkNDMMVQ1EHcgqoX4mZGFLGBCj8qQizuAwDXU8yXXzpOTgAAAABJRU5ErkJggg==',
			'600C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7WAMYAhimMEwNQBITmcIYwhDKECCCJBbQwtrK6OjowIIs1iDS6NoQ6IDsvsioaStTV0VmIbsvZAqKOojeVmximHZgcws2Nw9U+FERYnEfAKNPyvp7rZQRAAAAAElFTkSuQmCC',
			'CF6C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7WENEQx1CGaYGIImJtIo0MDo6BIggiQU0ijSwNjg6sCCLNYDEGB2Q3Re1amrY0qkrs5DdB1bn6OjAgKE3EFWsESKGbAc2t7CGAHlobh6o8KMixOI+AEjly3hq/+zFAAAAAElFTkSuQmCC',
			'C10E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7WEMYAhimMIYGIImJtDIGMIQyOiCrC2hkDWB0dEQVa2AIYG0IhImBnRQFREtXRYZmIbkPTR1usUYGDDtEWhkw3MIawhqK7uaBCj8qQizuAwDnu8f4R8XbgwAAAABJRU5ErkJggg==',
			'E0EB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAVklEQVR4nGNYhQEaGAYTpIn7QkMYAlhDHUMdkMQCGhhDWBsYHQJQxFhbQWIiKGIija4IdWAnhUZNW5kaujI0C8l9aOpQxEQI2oHpFmxuHqjwoyLE4j4AUxLLgJG7xMUAAAAASUVORK5CYII=',
			'F961' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QkMZQxhCGVqRxQIaWFsZHR2mooqJNLo2OIRiisH1gp0UGrV0aerUVUuR3RfQwBjo6uiAZgcDUG8AmhgLFjGwW9DEwG4ODRgE4UdFiMV9APR9zbY97V4uAAAAAElFTkSuQmCC',
			'3B52' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7RANEQ1hDHaY6IIkFTBFpZW1gCAhAVtkq0ujawOgggiwGUjeVoUEEyX0ro6aGLc3MWhWF7D6gOqCpjQ5o5jk0BLQyYNgRMIUBzS2Mjg4B6G5mCGUMDRkE4UdFiMV9AInmzG8AC1KEAAAAAElFTkSuQmCC',
			'038C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7GB1YQxhCGaYGIImxBoi0Mjo6BIggiYlMYWh0bQh0YEESC2hlAKpzdEB2X9TSVWGrQldmIbsPTR1MDGweAwE7sLkFm5sHKvyoCLG4DwCzdspCFbsLiQAAAABJRU5ErkJggg==',
			'7DC5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QkNFQxhCHUMDkEVbRVoZHQIdGFDFGl0bBFHFpoDEGF0dkN0XNW1l6qqVUVFI7mN0AKljaBBB0svagCkm0gCxA1ksoAHkloCAABQxkJsdpjoMgvCjIsTiPgCwjswOp8oWTgAAAABJRU5ErkJggg==',
			'1EEF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAATklEQVR4nGNYhQEaGAYTpIn7GB1EQ1lDHUNDkMRYHUQaWIEyyOpEsYgxooqBnbQya2rY0tCVoVlI7mMkrJckMdEQsJtRxAYq/KgIsbgPABNGxZ3LSaNyAAAAAElFTkSuQmCC',
			'B9C0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7QgMYQxhCHVqRxQKmsLYyOgRMdUAWaxVpdG0QCAhAUQcSY3QQQXJfaNTSpamrVmZNQ3JfwBTGQCR1UPMYGjHFWLDYgekWbG4eqPCjIsTiPgA8Js3NWYlfzQAAAABJRU5ErkJggg==',
			'21BE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7WAMYAlhDGUMDkMREpjAGsDY6OiCrC2hlDWBtCEQRY2hlQFYHcdO0VVFLQ1eGZiG7L4ABwzxGBwYM81gbMMVEGjD1hoayhqK7eaDCj4oQi/sAwffH2mGtpIoAAAAASUVORK5CYII=',
			'AF0F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7GB1EQx2mMIaGIImxBog0MIQyOiCrE5ki0sDo6IgiFtAq0sDaEAgTAzspaunUsKWrIkOzkNyHpg4MQ0MxxUDqsNmB7haw2BRUsYEKPypCLO4DAEQYyf6Xy7niAAAAAElFTkSuQmCC',
			'CB28' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7WENEQxhCGaY6IImJtIq0Mjo6BAQgiQU0ijS6NgQ6iCCLAVUCSZg6sJOiVk0NW7Uya2oWkvvA6loZUM1rEGl0mMKIah7QDocAVDGwWxxQ9YLczBoagOLmgQo/KkIs7gMAxTbMmkMsOaIAAAAASUVORK5CYII=',
			'6B5B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7WANEQ1hDHUMdkMREpoi0sjYwOgQgiQW0iDS6AsVEkMUagOqmwtWBnRQZNTVsaWZmaBaS+0KA5jE0BKKa1yrS6AAUE0ETc0UTA7mF0dERRS/IzQyhjChuHqjwoyLE4j4A4vHL/cT5PQIAAAAASUVORK5CYII=',
			'E9E7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7QkMYQ1hDHUNDkMQCGlhbWYG0CIqYSKMrDrEAJPeFRi1dmhq6amUWkvsCGhgDgepaGVD0MoD0TkEVYwGJBaCKgdzC6IDFzShiAxV+VIRY3AcAEaLMoYZrrSYAAAAASUVORK5CYII=',
			'C03B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7WEMYAhhDGUMdkMREWhlDWBsdHQKQxAIaWVsZGgIdRJDFGkQaHRDqwE6KWjVtZdbUlaFZSO5DU4cQQzcPix3Y3ILNzQMVflSEWNwHAGfvzGG9wDKhAAAAAElFTkSuQmCC',
			'CA3B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WEMYAhhDGUMdkMREWhlDWBsdHQKQxAIaWVsZGgIdRJDFGkQaHRDqwE6KWjVtZdbUlaFZSO5DUwcVEwXaiWZeI1AdmphIq0ijK5pe1hCRRkc0Nw9U+FERYnEfAHt8zYbIk6Z0AAAAAElFTkSuQmCC',
			'DE7C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QgNEQ1lDA6YGIIkFTBEBkQEiyGKtIF6gAwu6WKOjA7L7opZODVu1dGUWsvvA6qYwOjCg6w3AFGN0YES1A+gWVqBKZLeA3dzAgOLmgQo/KkIs7gMA2XzMdX41ttIAAAAASUVORK5CYII=',
			'FC56' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkMZQ1lDHaY6IIkFNLA2ujYwBASgiIk0uDYwOgigibFOZXRAdl9o1LRVSzMzU7OQ3AdSx9AQiGEeUMxBBMMOdDHWRkdHBzS9jKEMoQwobh6o8KMixOI+ANrjzZf2eM0wAAAAAElFTkSuQmCC',
			'3884' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7RAMYQxhCGRoCkMQCprC2Mjo6NCKLMbSKNLo2BLSiiEHUTQlAct/KqJVhq0JXRUUhuw+sztEB07zA0BBMO7C5BUUMm5sHKvyoCLG4DwC0fM168jKukgAAAABJRU5ErkJggg==',
			'2638' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7WAMYQxhDGaY6IImJTGFtZW10CAhAEgtoFWlkaAh0EEHW3QrkIdRB3DRtWtiqqaumZiG7L0C0lQHNPEYHkUYHNPNYGzDFRBow3RIaiunmgQo/KkIs7gMAigTMiM+tBz8AAAAASUVORK5CYII=',
			'D09D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QgMYAhhCGUMdkMQCpjCGMDo6OgQgi7WytrI2BDqIoIiJNLoixMBOilo6bWVmZmTWNCT3gdQ5hGDqdcAwj7WVEV0Mi1uwuXmgwo+KEIv7AGqIzGDXSfUTAAAAAElFTkSuQmCC',
			'5AE6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7QkMYAlhDHaY6IIkFNDCGsDYwBASgiLG2sjYwOgggiQUGiDS6AsWQ3Rc2bdrK1NCVqVnI7msFq0Mxj6FVNBSkVwTZDog6FDGRKSAxVLewguxFc/NAhR8VIRb3AQDKr8wO592JlQAAAABJRU5ErkJggg==',
			'1BFB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXElEQVR4nGNYhQEaGAYTpIn7GB1EQ1hDA0MdkMRYHURaWYEyAUhiog4ija5AMREUvSjqwE5amTU1bGnoytAsJPcxYjGPEbt5hOyAuCUE6OYGRhQ3D1T4URFicR8AIeTIKhXAP+QAAAAASUVORK5CYII=',
			'607A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7WAMYAlhDA1qRxUSmMIYwNARMdUASC2hhBaoJCAhAFmsQaXRodHQQQXJfZNS0lVlLV2ZNQ3JfyBSguimMMHUQva1AsQDG0BAUMdZWRgdUdSC3sDagioHdjCY2UOFHRYjFfQDbxsuJC7sHIQAAAABJRU5ErkJggg==',
			'C315' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7WENYQximMIYGIImJtIq0MoQwOiCrC2hkaHREF2tgaAXqdXVAcl/UqlVhq6atjIpCch9EHdBcVL2NDuhijSAxRgcRdLdMYQhAdh/IzYyhDlMdBkH4URFicR8A4ATLaoOl5vwAAAAASUVORK5CYII=',
			'8961' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7WAMYQxhCGVqRxUSmsLYyOjpMRRYLaBVpdG1wCEVVBxKD6wU7aWnU0qWpU1ctRXafyBTGQFdHh1ZU8xiAegPQxFgwxKBuQRGDujk0YBCEHxUhFvcBAKeGzKW7mysBAAAAAElFTkSuQmCC',
			'EE1F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAUUlEQVR4nGNYhQEaGAYTpIn7QkNEQxmmMIaGIIkFNIg0MIQwOjCgiTFiEQPqhYmBnRQaNTVs1bSVoVlI7kNTR7EYyM2MoY4oYgMVflSEWNwHAE8LydhekhxaAAAAAElFTkSuQmCC',
			'8C35' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WAMYQ0EwAElMZApro2ujowOyuoBWkQaHhkAUMZEpIg0MjY6uDkjuWxo1bdWqqSujopDcB1Hn0CCCZh6QxBAD2SHSgO4WhwBk90HczDDVYRCEHxUhFvcBAGKpzVUVToJvAAAAAElFTkSuQmCC',
			'D0B5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QgMYAlhDGUMDkMQCpjCGsDY6OiCrC2hlbWVtCEQTE2l0bXR0dUByX9TSaStTQ1dGRSG5D6LOoUEEXW9DAJoYxA4RDLc4BCC7D+JmhqkOgyD8qAixuA8AxOjNoxSw7jIAAAAASUVORK5CYII=',
			'CDD6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXElEQVR4nGNYhQEaGAYTpIn7WENEQ1hDGaY6IImJtIq0sjY6BAQgiQU0ijS6NgQ6CCCLNUDEkN0XtWraytRVkalZSO6DqkM1D6pXBIsdIgTcgs3NAxV+VIRY3AcAgcLOGBXJTnoAAAAASUVORK5CYII=',
			'8DDE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAVklEQVR4nGNYhQEaGAYTpIn7WANEQ1hDGUMDkMREpoi0sjY6OiCrC2gVaXRtCEQRA6pDFgM7aWnUtJWpqyJDs5Dch6YOp3k47MBwCzY3D1T4URFicR8AFhHMIh3bQ1wAAAAASUVORK5CYII=',
			'80FC' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7WAMYAlhDA6YGIImJTGEMYW1gCBBBEgtoZW1lbWB0YEFRJ9LoChRDdt/SqGkrU0NXZiG7D00d1DxsYtjswHQL2M0NDChuHqjwoyLE4j4A6V3KVw31boAAAAAASUVORK5CYII=',
			'B634' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QgMYQxhDGRoCkMQCprC2sjY6NKKItYo0gkhUdSINDI0OUwKQ3BcaNS1s1dRVUVFI7guYItrK0OjogG6eQ0NgaAiGWAA2t6CIYXPzQIUfFSEW9wEAGmPQNmGm4ZIAAAAASUVORK5CYII=',
			'A532' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7GB1EQxlDGaY6IImxBog0sDY6BAQgiYlMEQGSgQ4iSGIBrSIhDI0ODSJI7otaOnXpqqlAGsl9Aa1AVSCIpDc0FKQTKINqHkhsCqoYayvILahijCGMQFeHDILwoyLE4j4ALgnODjZnrlMAAAAASUVORK5CYII=',
			'498D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpI37pjCGMIQyhjogi4WwtjI6OjoEIIkxhog0ujYEOoggibFOEWl0BKoTQXLftGlLl2aFrsyahuS+gCmMgUjqwDA0lAHDPIYpLFjEMN2C1c0DFX7Ug1jcBwB6o8rxUz+eqwAAAABJRU5ErkJggg==',
			'A0EA' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7GB0YAlhDHVqRxVgDGENYGximOiCJiUxhbQWKBQQgiQW0ijS6Ak0QQXJf1NJpK1NDV2ZNQ3IfmjowDA0Fi4WGoJgHsgNVXUAryC3oYiA3O6KIDVT4URFicR8AjILK/mNPkJUAAAAASUVORK5CYII=',
			'5629' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeUlEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGaY6IIkFNLC2Mjo6BASgiIk0sjYEOoggiQUGgHhwMbCTwqZNC1u1MisqDNl9raKtDK0MU5H1MrSKNDpMYWhAFgsAiQUwoNghMgXoFgcGFLewBjCGsIYGoLh5oMKPihCL+wBkc8t2U340agAAAABJRU5ErkJggg=='        
        );
        $this->text = array_rand( $images );
        return $images[ $this->text ] ;    
    }
    
    function out_processing_gif(){
        $image = dirname(__FILE__) . '/processing.gif';
        $base64_image = "R0lGODlhFAAUALMIAPh2AP+TMsZiALlcAKNOAOp4ANVqAP+PFv///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFCgAIACwAAAAAFAAUAAAEUxDJSau9iBDMtebTMEjehgTBJYqkiaLWOlZvGs8WDO6UIPCHw8TnAwWDEuKPcxQml0Ynj2cwYACAS7VqwWItWyuiUJB4s2AxmWxGg9bl6YQtl0cAACH5BAUKAAgALAEAAQASABIAAAROEMkpx6A4W5upENUmEQT2feFIltMJYivbvhnZ3Z1h4FMQIDodz+cL7nDEn5CH8DGZhcLtcMBEoxkqlXKVIgAAibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkphaA4W5upMdUmDQP2feFIltMJYivbvhnZ3V1R4BNBIDodz+cL7nDEn5CH8DGZAMAtEMBEoxkqlXKVIg4HibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpjaE4W5tpKdUmCQL2feFIltMJYivbvhnZ3R0A4NMwIDodz+cL7nDEn5CH8DGZh8ONQMBEoxkqlXKVIgIBibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpS6E4W5spANUmGQb2feFIltMJYivbvhnZ3d1x4JMgIDodz+cL7nDEn5CH8DGZgcBtMMBEoxkqlXKVIggEibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpAaA4W5vpOdUmFQX2feFIltMJYivbvhnZ3V0Q4JNhIDodz+cL7nDEn5CH8DGZBMJNIMBEoxkqlXKVIgYDibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpz6E4W5tpCNUmAQD2feFIltMJYivbvhnZ3R1B4FNRIDodz+cL7nDEn5CH8DGZg8HNYMBEoxkqlXKVIgQCibbK9YLBYvLtHH5K0J0IACH5BAkKAAgALAEAAQASABIAAAROEMkpQ6A4W5spIdUmHQf2feFIltMJYivbvhnZ3d0w4BMAIDodz+cL7nDEn5CH8DGZAsGtUMBEoxkqlXKVIgwGibbK9YLBYvLtHH5K0J0IADs=";
        $binary = is_file($image) ? join("",file($image)) : base64_decode($base64_image); 
        header("Cache-Control: post-check=0, pre-check=0, max-age=0, no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: image/gif");
        echo $binary;
    }

}
# end of class phpfmgImage
# ------------------------------------------------------
# end of module : captcha


# module user
# ------------------------------------------------------
function phpfmg_user_isLogin(){
    return ( isset($_SESSION['authenticated']) && true === $_SESSION['authenticated'] );
}


function phpfmg_user_logout(){
    session_destroy();
    header("Location: admin.php");
}

function phpfmg_user_login()
{
    if( phpfmg_user_isLogin() ){
        return true ;
    };
    
    $sErr = "" ;
    if( 'Y' == $_POST['formmail_submit'] ){
        if(
            defined( 'PHPFMG_USER' ) && strtolower(PHPFMG_USER) == strtolower($_POST['Username']) &&
            defined( 'PHPFMG_PW' )   && strtolower(PHPFMG_PW) == strtolower($_POST['Password']) 
        ){
             $_SESSION['authenticated'] = true ;
             return true ;
             
        }else{
            $sErr = 'Login failed. Please try again.';
        }
    };
    
    // show login form 
    phpfmg_admin_header();
?>
<form name="frmFormMail" action="" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:380px;height:260px;">
<fieldset style="padding:18px;" >
<table cellspacing='3' cellpadding='3' border='0' >
	<tr>
		<td class="form_field" valign='top' align='right'>Email :</td>
		<td class="form_text">
            <input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" class='text_box' >
		</td>
	</tr>

	<tr>
		<td class="form_field" valign='top' align='right'>Password :</td>
		<td class="form_text">
            <input type="password" name="Password"  value="" class='text_box'>
		</td>
	</tr>

	<tr><td colspan=3 align='center'>
        <input type='submit' value='Login'><br><br>
        <?php if( $sErr ) echo "<span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
        <a href="admin.php?mod=mail&func=request_password">I forgot my password</a>   
    </td></tr>
</table>
</fieldset>
</div>
<script type="text/javascript">
    document.frmFormMail.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();
}


function phpfmg_mail_request_password(){
    $sErr = '';
    if( $_POST['formmail_submit'] == 'Y' ){
        if( strtoupper(trim($_POST['Username'])) == strtoupper(trim(PHPFMG_USER)) ){
            phpfmg_mail_password();
            exit;
        }else{
            $sErr = "Failed to verify your email.";
        };
    };
    
    $n1 = strpos(PHPFMG_USER,'@');
    $n2 = strrpos(PHPFMG_USER,'.');
    $email = substr(PHPFMG_USER,0,1) . str_repeat('*',$n1-1) . 
            '@' . substr(PHPFMG_USER,$n1+1,1) . str_repeat('*',$n2-$n1-2) . 
            '.' . substr(PHPFMG_USER,$n2+1,1) . str_repeat('*',strlen(PHPFMG_USER)-$n2-2) ;


    phpfmg_admin_header("Request Password of Email Form Admin Panel");
?>
<form name="frmRequestPassword" action="admin.php?mod=mail&func=request_password" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:580px;height:260px;text-align:left;">
<fieldset style="padding:18px;" >
<legend>Request Password</legend>
Enter Email Address <b><?php echo strtoupper($email) ;?></b>:<br />
<input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" style="width:380px;">
<input type='submit' value='Verify'><br>
The password will be sent to this email address. 
<?php if( $sErr ) echo "<br /><br /><span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
</fieldset>
</div>
<script type="text/javascript">
    document.frmRequestPassword.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();    
}


function phpfmg_mail_password(){
    phpfmg_admin_header();
    if( defined( 'PHPFMG_USER' ) && defined( 'PHPFMG_PW' ) ){
        $body = "Here is the password for your form admin panel:\n\nUsername: " . PHPFMG_USER . "\nPassword: " . PHPFMG_PW . "\n\n" ;
        if( 'html' == PHPFMG_MAIL_TYPE )
            $body = nl2br($body);
        mailAttachments( PHPFMG_USER, "Password for Your Form Admin Panel", $body, PHPFMG_USER, 'You', "You <" . PHPFMG_USER . ">" );
        echo "<center>Your password has been sent.<br><br><a href='admin.php'>Click here to login again</a></center>";
    };   
    phpfmg_admin_footer();
}


function phpfmg_writable_check(){
 
    if( is_writable( dirname(PHPFMG_SAVE_FILE) ) && is_writable( dirname(PHPFMG_EMAILS_LOGFILE) )  ){
        return ;
    };
?>
<style type="text/css">
    .fmg_warning{
        background-color: #F4F6E5;
        border: 1px dashed #ff0000;
        padding: 16px;
        color : black;
        margin: 10px;
        line-height: 180%;
        width:80%;
    }
    
    .fmg_warning_title{
        font-weight: bold;
    }

</style>
<br><br>
<div class="fmg_warning">
    <div class="fmg_warning_title">Your form data or email traffic log is NOT saving.</div>
    The form data (<?php echo PHPFMG_SAVE_FILE ?>) and email traffic log (<?php echo PHPFMG_EMAILS_LOGFILE?>) will be created automatically when the form is submitted. 
    However, the script doesn't have writable permission to create those files. In order to save your valuable information, please set the directory to writable.
     If you don't know how to do it, please ask for help from your web Administrator or Technical Support of your hosting company.   
</div>
<br><br>
<?php
}


function phpfmg_log_view(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    
    phpfmg_admin_header();
   
    $file = $files[$n];
    if( is_file($file) ){
        if( 1== $n ){
            echo "<pre>\n";
            echo join("",file($file) );
            echo "</pre>\n";
        }else{
            $man = new phpfmgDataManager();
            $man->displayRecords();
        };
     

    }else{
        echo "<b>No form data found.</b>";
    };
    phpfmg_admin_footer();
}


function phpfmg_log_download(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );

    $file = $files[$n];
    if( is_file($file) ){
        phpfmg_util_download( $file, PHPFMG_SAVE_FILE == $file ? 'form-data.csv' : 'email-traffics.txt', true, 1 ); // skip the first line
    }else{
        phpfmg_admin_header();
        echo "<b>No email traffic log found.</b>";
        phpfmg_admin_footer();
    };

}


function phpfmg_log_delete(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    phpfmg_admin_header();

    $file = $files[$n];
    if( is_file($file) ){
        echo unlink($file) ? "It has been deleted!" : "Failed to delete!" ;
    };
    phpfmg_admin_footer();
}


function phpfmg_util_download($file, $filename='', $toCSV = false, $skipN = 0 ){
    if (!is_file($file)) return false ;

    set_time_limit(0);


    $buffer = "";
    $i = 0 ;
    $fp = @fopen($file, 'rb');
    while( !feof($fp)) { 
        $i ++ ;
        $line = fgets($fp);
        if($i > $skipN){ // skip lines
            if( $toCSV ){ 
              $line = str_replace( chr(0x09), ',', $line );
              $buffer .= phpfmg_data2record( $line, false );
            }else{
                $buffer .= $line;
            };
        }; 
    }; 
    fclose ($fp);
  

    
    /*
        If the Content-Length is NOT THE SAME SIZE as the real conent output, Windows+IIS might be hung!!
    */
    $len = strlen($buffer);
    $filename = basename( '' == $filename ? $file : $filename );
    $file_extension = strtolower(substr(strrchr($filename,"."),1));

    switch( $file_extension ) {
        case "pdf": $ctype="application/pdf"; break;
        case "exe": $ctype="application/octet-stream"; break;
        case "zip": $ctype="application/zip"; break;
        case "doc": $ctype="application/msword"; break;
        case "xls": $ctype="application/vnd.ms-excel"; break;
        case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
        case "gif": $ctype="image/gif"; break;
        case "png": $ctype="image/png"; break;
        case "jpeg":
        case "jpg": $ctype="image/jpg"; break;
        case "mp3": $ctype="audio/mpeg"; break;
        case "wav": $ctype="audio/x-wav"; break;
        case "mpeg":
        case "mpg":
        case "mpe": $ctype="video/mpeg"; break;
        case "mov": $ctype="video/quicktime"; break;
        case "avi": $ctype="video/x-msvideo"; break;
        //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
        case "php":
        case "htm":
        case "html": 
                $ctype="text/plain"; break;
        default: 
            $ctype="application/x-download";
    }
                                            

    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public"); 
    header("Content-Description: File Transfer");
    //Use the switch-generated Content-Type
    header("Content-Type: $ctype");
    //Force the download
    header("Content-Disposition: attachment; filename=".$filename.";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$len);
    
    while (@ob_end_clean()); // no output buffering !
    flush();
    echo $buffer ;
    
    return true;
 
    
}
?>