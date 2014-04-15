<?php
require_once( dirname(__FILE__).'/form.lib.php' );

define( 'PHPFMG_USER', "email@julian.id.au" ); // must be a email address. for sending password to you.
define( 'PHPFMG_PW', "f630fe" );

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

<div class="fmg_title">
    3. Form Generator
</div>
<div class="fmg_text">
    <a href="http://www.formmail-maker.com/generator.php" onclick="document.frmFormMail.submit(); return false;" title="<?php echo htmlspecialchars(PHPFMG_SUBJECT);?>">Edit Form</a> &nbsp;&nbsp;
    <a href="http://www.formmail-maker.com/generator.php" >New Form</a>
</div>
    <form name="frmFormMail" action='http://www.formmail-maker.com/generator.php' method='post' enctype='multipart/form-data'>
    <input type="hidden" name="uuid" value="<?php echo PHPFMG_ID; ?>">
    <input type="hidden" name="external_ini" value="<?php echo function_exists('phpfmg_formini') ?  phpfmg_formini() : ""; ?>">
    </form>

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
    <title><?php echo '' == $title ? '' : $title . ' | ' ; ?>PHP FormMail Admin Panel </title>
    <meta name="keywords" content="PHP FormMail Generator, PHP HTML form, send html email with attachment, PHP web form,  Free Form, Form Builder, Form Creator, phpFormMailGen, Customized Web Forms, phpFormMailGenerator,formmail.php, formmail.pl, formMail Generator, ASP Formmail, ASP form, PHP Form, Generator, phpFormGen, phpFormGenerator, anti-spam, web hosting">
    <meta name="description" content="PHP formMail Generator - A tool to ceate ready-to-use web forms in a flash. Validating form with CAPTCHA security image, send html email with attachments, send auto response email copy, log email traffics, save and download form data in Excel. ">
    <meta name="generator" content="PHP Mail Form Generator, phpfmg.sourceforge.net">

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
	:: <a href="http://phpfmg.sourceforge.net" target="_blank" title="Free Mailform Maker: Create read-to-use Web Forms in a flash. Including validating form with CAPTCHA security image, send html email with attachments, send auto response email copy, log email traffics, save and download form data in Excel. " style="color:#cccccc;font-weight:bold;text-decoration:none;">PHP FormMail Generator</a> ::
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
			'2E3C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7WANEQxlDGaYGIImJTBFpYG10CBBBEgtoBfECHViQdYPEGh0dUNw3bWrYqqkrs1DcF4CiDgwZHSDmobilAdMOkQZMt4SGYrp5oMKPihCL+wC8ccsGiAH1NgAAAABJRU5ErkJggg==',
			'3C96' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7RAMYQxlCGaY6IIkFTGFtdHR0CAhAVtkq0uDaEOgggCw2RaSBFSiG7L6VUdNWrcyMTM1Cdh9QHUNIIIZ5DEC9Imhijmhi2NyCzc0DFX5UhFjcBwAAkswR0WHKywAAAABJRU5ErkJggg==',
			'A3D5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7GB1YQ1hDGUMDkMRYA0RaWRsdHZDViUxhaHRtCEQRC2hlaGVtCHR1QHJf1NJVYUtXRUZFIbkPoi6gQQRJb2goyDxUMaA6sB2oYiC3OAQEoIiB3Mww1WEQhB8VIRb3AQCE2Mzx5NmhTwAAAABJRU5ErkJggg==',
			'9EFD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWUlEQVR4nGNYhQEaGAYTpIn7WANEQ1lDA0MdkMREpog0sDYwOgQgiQW0QsREcIuBnTRt6tSwpaErs6YhuY/VFVMvAxbzBLCIYXML2M0NjChuHqjwoyLE4j4APZzJtQqNitAAAAAASUVORK5CYII=',
			'3CFB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7RAMYQ1lDA0MdkMQCprA2ujYwOgQgq2wVaQCJiSCLTRFpYEWoAztpZdS0VUtDV4ZmIbsPVR3cPFZ087DYgc0tYDc3MKK4eaDCj4oQi/sA4u3LFou+J1UAAAAASUVORK5CYII=',
			'D4A6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7QgMYWhmmMEx1QBILAPIZQhkCApDFWhlCGR0dHQRQxBhdWRsCHZDdF7UUCFZFpmYhuS+gVaQVqA7NPNFQ19BABxFUO0DqUMWmgMQCUPSC3AwUQ3HzQIUfFSEW9wEA3STN0i2RdOwAAAAASUVORK5CYII=',
			'CA7E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WEMYAlhDA0MDkMREWhlDGBoCHZDVBTSytmKINYg0OjQ6wsTATopaNW1l1tKVoVlI7gOrm8KIplc01CEATaxRBGgaqphIq0ijawOqGGsIWAzFzQMVflSEWNwHAAJey0r0fWy+AAAAAElFTkSuQmCC',
			'162D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7GB0YQxhCGUMdkMRYHVhbGR0dHQKQxEQdRBpZGwIdRFD0gnhwMbCTVmZNC1u1MjNrGpL7GB1EWxlaGdH1NjpMwSIWgC4GdIsDI6pbQhhDWEMDUdw8UOFHRYjFfQBHyMeBVLgUhAAAAABJRU5ErkJggg==',
			'E9CE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXElEQVR4nGNYhQEaGAYTpIn7QkMYQxhCHUMDkMQCGlhbGR0CHRhQxEQaXRsEsYgxwsTATgqNWro0ddXK0Cwk9wU0MAYiqYOKMTRiirFgsQPTLdjcPFDhR0WIxX0AKYnLVXBoYNMAAAAASUVORK5CYII=',
			'4454' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpI37pjC0soY6NAQgi4UwTGVtYGhEFmMMYQgFirUii7FOYXRlncowJQDJfdOmLV26NDMrKgrJfQFTRFoZGgIdkPWGhooCbQ0MDUF3C9AlAWhijI4OGGIMoQyoYgMVftSDWNwHACczzSPBeYDjAAAAAElFTkSuQmCC',
			'1298' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7GB0YQxhCGaY6IImxOrC2Mjo6BAQgiYk6iDS6NgQ6iKDoZQCKBcDUgZ20MmvV0pWZUVOzkNwHVDeFISQAxTygWAADhnlAiCHG2oDhlhDRUAc0Nw9U+FERYnEfAHDayTC8lW54AAAAAElFTkSuQmCC',
			'2D3F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7WANEQxhDGUNDkMREpoi0sjY6OiCrC2gVaXRoCEQRYwCJIdRB3DRt2sqsqStDs5DdF4CiDgwZHTDNY23AFBNpwHRLaCjYzahuGaDwoyLE4j4AuK/K5f5vysYAAAAASUVORK5CYII=',
			'02C3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7GB0YQxhCHUIdkMRYA1hbGR0CHQKQxESmiDS6Ngg0iCCJBbQyAMWANJL7opauWrp01aqlWUjuA6qbwopQBxMLAImJoNjB6MCKZgfQLQ3obmF0EAW5GMXNAxV+VIRY3AcAFw/MEttKDTYAAAAASUVORK5CYII=',
			'BF24' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QgNEQx1CGRoCkMQCpog0MDo6NKKItYo0sAJJdHUgMgDJfaFRU8NWrcyKikJyH1hdK6MDunkMUxhDQ9DFArC4xQFVLDQA6JbQABSxgQo/KkIs7gMAjsDOzgCja8QAAAAASUVORK5CYII=',
			'003C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7GB0YAhhDGaYGIImxBjCGsDY6BIggiYlMYW1laAh0YEESC2gVaXRodHRAdl/U0mkrs6auzEJ2H5o6hBjQPAYCdmBzCzY3D1T4URFicR8A2xLLJlX6rRUAAAAASUVORK5CYII=',
			'F806' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXElEQVR4nGNYhQEaGAYTpIn7QkMZQximMEx1QBILaGBtZQhlCAhAERNpdHR0dBBAU8faEOiA7L7QqJVhS1dFpmYhuQ+qDsM8V6BeESx2iBB0C6abByr8qAixuA8AGj/NB+wvXr4AAAAASUVORK5CYII=',
			'8A7B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WAMYAlhDA0MdkMREpjCGMDQEOgQgiQW0sraCxERQ1Ik0OjQ6wtSBnbQ0atrKrKUrQ7OQ3AdWN4URzTzRUIcARhTzAlpFgKYxYtjh2oCqlzUALIbi5oEKPypCLO4DAHC6zHTjDug6AAAAAElFTkSuQmCC',
			'B09A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7QgMYAhhCGVqRxQKmMIYwOjpMdUAWa2VtZW0ICAhAUSfS6NoQ6CCC5L7QqGkrMzMjs6YhuQ+kziEErg5qHlCsITA0BM0OxgY0dWC3OKKIQdzMiCI2UOFHRYjFfQDMwMyEzuS39AAAAABJRU5ErkJggg==',
			'70EB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7QkMZAlhDHUMdkEVbGUNYGxgdAlDEWFtBYiLIYlNEGl0R6iBuipq2MjV0ZWgWkvuAupDVgSFrA0QM2TyRBkw7Ahow3QJkY7p5gMKPihCL+wBtW8oVSQ2pmgAAAABJRU5ErkJggg==',
			'CAE1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7WEMYAlhDHVqRxURaGUNYGximIosFNLK2AsVCUcQaRBpdGxhgesFOilo1bWVq6KqlyO5DUwcVEw3FEGvEVCfSiinGGgIUC3UIDRgE4UdFiMV9AHKKzL0rMVhrAAAAAElFTkSuQmCC',
			'A09E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7GB0YAhhCGUMDkMRYAxhDGB0dHZDViUxhbWVtCEQRC2gVaXRFiIGdFLV02srMzMjQLCT3gdQ5hKDqDQ0FimGYx9rKiCGG6ZaAVkw3D1T4URFicR8AKzfKEX0IjRcAAAAASUVORK5CYII=',
			'1F39' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7GB1EQx1DGaY6IImxOog0sDY6BAQgiYkCxRgaAsEkQi+Q1+gIEwM7aWXW1LBVU1dFhSG5D6LOYSqG3oaABixiGHZguCVEpIERzc0DFX5UhFjcBwBeo8oEbdifPAAAAABJRU5ErkJggg==',
			'BBCF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7QgNEQxhCHUNDkMQCpoi0MjoEOiCrC2gVaXRtEEQVA6pjbWCEiYGdFBo1NWzpqpWhWUjuQ1OHZB42MUw70N0CdTOK2ECFHxUhFvcBAFOoy2GDzbHgAAAAAElFTkSuQmCC',
			'A780' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7GB1EQx1CGVqRxVgDGBodHR2mOiCJiUxhaHRtCAgIQBILaGVoZQQqFEFyX9TSVdNWha7MmobkPqC6ACR1YBgayujA2hCIIhbQytrAimGHSAMjmltAYgxobh6o8KMixOI+ACRRzFY3sfWdAAAAAElFTkSuQmCC',
			'BECE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWklEQVR4nGNYhQEaGAYTpIn7QgNEQxlCHUMDkMQCpog0MDoEOiCrC2gVaWBtEEQVmwISY4SJgZ0UGjU1bOmqlaFZSO5DU4dkHjYxTDvQ3YLNzQMVflSEWNwHAKNlyu0uMEjEAAAAAElFTkSuQmCC',
			'FA51' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QkMZAlhDHVqRxQIaGENYGximooqxtgLFQlHFRBpdpzLA9IKdFBo1bWVqZtZSZPeB1Dk0BKDZIRqKKQY0D4uYoyO6+4DmhTKEBgyC8KMixOI+ACJVziPpqRLFAAAAAElFTkSuQmCC',
			'303A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7RAMYAhhDGVqRxQKmMIawNjpMdUBW2coKVBMQEIAsNkWk0aHR0UEEyX0ro6atzJq6MmsasvtQ1UHNA4o1BIaGYNgRiKIO4hZUvRA3M6KaN0DhR0WIxX0A7Q/L0rwaFWQAAAAASUVORK5CYII=',
			'C877' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WEMYQ1hDA0NDkMREWllbGRoCGkSQxAIaRRod0MUagOrAogj3Ra1aGbZq6aqVWUjuA6ubwtDKgKIXaF4AUBTNDkcHhgAGNLewNjA6YLgZTWygwo+KEIv7AAgrzGjcNmI0AAAAAElFTkSuQmCC',
			'DBEE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAUklEQVR4nGNYhQEaGAYTpIn7QgNEQ1hDHUMDkMQCpoi0sjYwOiCrC2gVaXTFFENWB3ZS1NKpYUtDV4ZmIbkPTR0+8zDFsLgFm5sHKvyoCLG4DwBB4ctxmHzqggAAAABJRU5ErkJggg==',
			'FC29' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QkMZQxlCGaY6IIkFNLA2Ojo6BASgiIk0uDYEOoigiTEgxMBOCo2atmrVyqyoMCT3gdW1MkzF0DuFoQFdzCGAAc0OoFscGNDcwhjKGhqA4uaBCj8qQizuAwCqn81imqVfzgAAAABJRU5ErkJggg==',
			'E3CF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWklEQVR4nGNYhQEaGAYTpIn7QkNYQxhCHUNDkMQCGkRaGR0CHRhQxBgaXRsE0cVaWRsYYWJgJ4VGrQpbumplaBaS+9DUIZmHTQzdDky3QN2MIjZQ4UdFiMV9AFrWypt3TtkjAAAAAElFTkSuQmCC',
			'2373' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nM2QsQ2AMAwEn8IbeCCzgZEIQzCFKbJBxAZpPCUpKBxBCQJ/9/p/nQy/nOFPeoWPlGZKmiR4XDjDJtHgacYmpsaxnXG6gW/3xavXNfJpyxVY3BukNRXdHhm2UXqPjTO1dOym1JgNHfNX/3tQN3wHn2vMUfSQofgAAAAASUVORK5CYII=',
			'4C30' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpI37pjCGMoYytKKIhbA2ujY6THVAEmMMEWlwaAgICEASY50i0sDQ6OggguS+adOmrVo1dWXWNCT3BaCqA8PQUBAvEEWMYQqmHQxTMN2C1c0DFX7Ug1jcBwCkis14MU9BWgAAAABJRU5ErkJggg==',
			'FBD0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAV0lEQVR4nGNYhQEaGAYTpIn7QkNFQ1hDGVqRxQIaRFpZGx2mOqCKNbo2BAQEoKtrCHQQQXJfaNTUsKWrIrOmIbkPTR2SedjEsNiB4RZMNw9U+FERYnEfAFISzsh4UlmiAAAAAElFTkSuQmCC',
			'5183' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QkMYAhhCGUIdkMQCGhgDGB0dHQJQxFgDWIGkCJJYYAADUJ1DQwCS+8KmrYpaFbpqaRay+1pR1MHF0M0LwCImMoUBwy1Al4Siu3mgwo+KEIv7AB+zynJsbEPGAAAAAElFTkSuQmCC',
			'20F8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7WAMYAlhDA6Y6IImJTGEMYW1gCAhAEgtoZW1lbWB0EEHW3SrS6IpQB3HTtGkrU0NXTc1Cdl8AijowBJoEFEM1j7UB0w6RBky3hIYC3dzAgOLmgQo/KkIs7gMAX9PKsKb/cqQAAAAASUVORK5CYII=',
			'0314' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7GB1YQximMDQEIImxBoi0MoQwNCKLiUxhaHQMYWhFFgtoZWgF6p0SgOS+qKWrwlZNWxUVheQ+iDpGBzS9jQ5TGEND0OxwwOYWNDGQmxlDHVDEBir8qAixuA8AyzTM0DIP/GEAAAAASUVORK5CYII=',
			'8A9B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WAMYAhhCGUMdkMREpjCGMDo6OgQgiQW0srayNgQ6iKCoE2l0BYoFILlvadS0lZmZkaFZSO4DqXMICUQzTxRoJ6p5Aa0ijY5Y7HBEcwtrANA8NDcPVPhREWJxHwDsTcxKX9tmqAAAAABJRU5ErkJggg==',
			'4B55' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpI37poiGsIY6hgYgi4WItLI2MDogq2MMEWl0RRNjnQJUN5XR1QHJfdOmTQ1bmpkZFYXkvgCgOiDZIIKkNzRUpNEBTYxhCsiOQAc0sVZGR4cAFPcB3cwQyjDVYTCEH/UgFvcBAAGGy4xMxQp6AAAAAElFTkSuQmCC',
			'884F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7WAMYQxgaHUNDkMREprC2MrQ6OiCrC2gVaXSYiioGVhcIFwM7aWnUyrCVmZmhWUjuA6ljbcQ0zzU0ENOORix2oIlB3YwiNlDhR0WIxX0AtPHK7pKI+LYAAAAASUVORK5CYII=',
			'FF5C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QkNFQ11DHaYGIIkFNIg0sDYwBIhgiDE6sKCLTWV0QHZfaNTUsKWZmVnI7gOpY2gIdGBA04tNjBUohm4Ho6MDhlsYQhlQ3DxQ4UdFiMV9AE8uzE3ZGL8DAAAAAElFTkSuQmCC',
			'9D63' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7WANEQxhCGUIdkMREpoi0Mjo6OgQgiQW0ijS6Njg0iGCIAWkk902bOm1l6tRVS7OQ3MfqClTn6NCAbB4DWG8AinkCWMSwuQWbmwcq/KgIsbgPAMa5zW3VAklWAAAAAElFTkSuQmCC',
			'66D6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDGaY6IImJTGFtZW10CAhAEgtoEWlkbQh0EEAWaxBpAIkhuy8yalrY0lWRqVlI7guZItoKVIdqXqtIoytQrwgBMWxuwebmgQo/KkIs7gMA8hjM7HtxKEkAAAAASUVORK5CYII=',
			'EAE6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7QkMYAlhDHaY6IIkFNDCGsDYwBASgiLG2sjYwOgigiIk0ugLFkN0XGjVtZWroytQsJPdB1aGZJxoK0iuCxTxMMVS3hIYAxdDcPFDhR0WIxX0A3Y/NDVzrvHgAAAAASUVORK5CYII=',
			'4EA6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpI37poiGMkxhmOqALBYi0sAQyhAQgCTGCBRjdHR0EEASY50i0sDaEOiA7L5p06aGLV0VmZqF5L4AiDoU80JDgWKhgQ4iKG6BmIcpFoCiF+RmoBiqmwcq/KgHsbgPAFOTy97B1A0mAAAAAElFTkSuQmCC',
			'70F0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QkMZAlhDA1pRRFsZQ1gbGKY6oIixtgLFAgKQxaaINLo2MDqIILsvatrK1NCVWdOQ3AdUgawODFkbMMVEGjDtCGjAdAtQPgAohurmAQo/KkIs7gMAZprK1RV3AzAAAAAASUVORK5CYII=',
			'9F2C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7WANEQx1CGaYGIImJTBFpYHR0CBBBEgtoFWlgbQh0YEETYwCKIbtv2tSpYatWZmYhu4/VFaiuldEBxWaQ3imoYgIgsQBGFDvAbnFgQHELK5DHGhqA4uaBCj8qQizuAwDsBMoysrW9fQAAAABJRU5ErkJggg==',
			'D22A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdklEQVR4nM2QsRGAIAxFk4INcJ9Y0Ic70riBToEFG6AbUMiU2nAHaqmn+d27V7wL5Mt5+NNe6RNGBwKhZhxVwJ4WqlnQs/HM3DCYyVvSVd+QcsrbOK1V3+FFCFi8whgiimsYEvDJi8ojtUy4EyO2YV/978Hd9O3qTMx2sL52TgAAAABJRU5ErkJggg==',
			'62F7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDA0NDkMREprC2soJoJLGAFpFGV3SxBgawWACS+yKjVi1dGrpqZRaS+0KmMEwBmteKbG9AK0MAUGwKqhijA1AsAFkM6JYG1gZGB1Q3i4a6ookNVPhREWJxHwCOCMtcexYCAQAAAABJRU5ErkJggg==',
			'6C3A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WAMYQxlDGVqRxUSmsDa6NjpMdUASC2gRaXBoCAgIQBZrEGlgaHR0EEFyX2TUtFWrpq7MmobkvpApKOogeltBvMDQEDQxh4ZAFHUQt6DqhbiZEUVsoMKPihCL+wDpKM1pXfcB2AAAAABJRU5ErkJggg==',
			'5966' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGaY6IIkFNLC2Mjo6BASgiIk0ujY4OgggiQUGgMQYHZDdFzZt6dLUqStTs5Dd18oY6OroiGIeQysDUG+ggwiyHa0sGGIiUzDdwhqA6eaBCj8qQizuAwBC5cwoTmz36wAAAABJRU5ErkJggg==',
			'5385' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7QkNYQxhCGUMDkMQCGkRaGR0dHRhQxBgaXRsCUcQCAxhA6lwdkNwXNm1V2KrQlVFRyO5rBalzaBBBtrkVZF4AilhAK8QOZDGRKSC3OAQgu481AORmhqkOgyD8qAixuA8ACkLLWyZUtzEAAAAASUVORK5CYII=',
			'4B9E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpI37poiGMIQyhgYgi4WItDI6Ojogq2MMEWl0bQhEEWOdItLKihADO2natKlhKzMjQ7OQ3BcAVMcQgqo3NFSk0QHNPIYpIo2OmGIYbsHq5oEKP+pBLO4DAF90yhFFDs9HAAAAAElFTkSuQmCC',
			'7023' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nM2QsQ2AQAhFoWCDG4jbgEIaR7gpsLgNjBtYnFNKCdFSo/yGvJCfF+C4jMGf8oqfKggoKEfaccJaWRKjTiZWIlvLws4k+s3baKPtLfgh+10Hi31kzlZIfcWou01iYu7CmFx8F1LJzh/978Hc+J2By8uxHB8BRAAAAABJRU5ErkJggg==',
			'F495' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7QkMZWhlCGUMDkMSA7KmMjo4ODKhioawNgWhijK5AMVcHJPeFRi1dujIzMioKyX0BDSKtDCFAEkWvaKhDA7oYQysj0A4MMUeHADT3Ad3MMNVhEIQfFSEW9wEABNTMUbCkQ9EAAAAASUVORK5CYII=',
			'F16C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7QkMZAhhCGaYGIIkFNDAGMDo6BIigiLEGsDY4OrCgiDEAxRgdkN0XGrUqaunUlVnI7gOrc3R0YMDQG4hVDN0OLG4JRXfzQIUfFSEW9wEAZEDKBhIVTgsAAAAASUVORK5CYII=',
			'5D1B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkNEQximMIY6IIkFNIi0MoQwOgSgijU6AsVEkMQCA0QaHabA1YGdFDZt2sqsaStDs5Dd14qiDkUM2bwALGIiU4BuQdPLGiAawhjqiOLmgQo/KkIs7gMA+JfL9/SA7y0AAAAASUVORK5CYII=',
			'2892' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAd0lEQVR4nM2QsQ2AMAwEncIbeKBQ0D9SQsEGbOEU3gDYAaYERGMEJUjxVz7J8ulpe4xSTfnFjxESZZqjYzKxhSYCjsGktNpF8dfGxgoV77es/ToO2+D9wEYJxf8IUY4ddnNRKY1i8kz0cvEs59M55FRBfx/mxW8HtJPLxbWcWFcAAAAASUVORK5CYII=',
			'9805' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WAMYQximMIYGIImJTGFtZQhldEBWF9Aq0ujo6IgmxtrK2hDo6oDkvmlTV4YtXRUZFYXkPlZXkLqABhFkm4HmuaKJCUDtEMFwC0MAsvsgbmaY6jAIwo+KEIv7ALJtyx+vMEuqAAAAAElFTkSuQmCC',
			'0E1E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7GB1EQxmmMIYGIImxBog0MIQwOiCrE5ki0sCIJhbQClQ3BS4GdlLU0qlhq6atDM1Cch+aOpxiIDvQxcBuQRMDuZkx1BHFzQMVflSEWNwHAHksyIIebKJFAAAAAElFTkSuQmCC',
			'3291' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7RAMYQxhCGVqRxQKmsLYyOjpMRVHZKtLo2hAQiiI2hQEkBtMLdtLKqFVLV2ZGLUVx3xQgDAloRTWPIYChAV2M0YERTQzolgagW1DERANEQx1CGUIDBkH4URFicR8AkrHLtDsveP0AAAAASUVORK5CYII=',
			'8EE5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7WANEQ1lDHUMDkMREpog0sDYwOiCrC2jFFIOqc3VAct/SqKlhS0NXRkUhuQ+iDkhjmIdNjNFBBMMOhgBk90Hc7DDVYRCEHxUhFvcBABCqyqys4Hp4AAAAAElFTkSuQmCC',
			'9890' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WAMYQxhCGVqRxUSmsLYyOjpMdUASC2gVaXRtCAgIQBFjbWVtCHQQQXLftKkrw1ZmRmZNQ3IfqytrK0MIXB0EAs1zaEAVEwCKOaLZgc0t2Nw8UOFHRYjFfQC6lsvJsFksSwAAAABJRU5ErkJggg==',
			'1FD4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7GB1EQ11DGRoCkMRYHUQaWBsdGpHFREFiDQGtASh6wWJTApDctzJratjSVVFRUUjug6gLdMDUGxgagmleA4a6RgcUMdEQoBiamwcq/KgIsbgPAMKcy+ctyYkwAAAAAElFTkSuQmCC',
			'B223' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nM2QsRGAIAxFk4INcJ9Q0KcwjSMwRSjYgBUoZEopiVrqaX73LvfzLtAvo/CnvOInjCsICE2MqysYAvHMis9RWb3Zg0yD8eQnW299Ty1NfmOvQgG1fcCD2r6CNOjphlMkNC7Ci0Rh4/zV/x7Mjd8BNJnNr6yPkH4AAAAASUVORK5CYII=',
			'FD41' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWklEQVR4nGNYhQEaGAYTpIn7QkNFQxgaHVqRxQIaRFoZWh2mook1Okx1CMUQC4TrBTspNGrayszMrKXI7gOpc8W0o9E1NABDzAGbWzDEwG4ODRgE4UdFiMV9ABDsz2eJoH4QAAAAAElFTkSuQmCC',
			'29C4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7WAMYQxhCHRoCkMREprC2MjoENCKLBbSKNLo2CLQiizGAxRimBCC7b9rSpamrVkVFIbsvgDHQtQFoIpJeRgcGoF7G0BBktzSwgOxAdUsD2C0oYqGhmG4eqPCjIsTiPgDNps138zSMYAAAAABJRU5ErkJggg==',
			'8898' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7WAMYQxhCGaY6IImJTGFtZXR0CAhAEgtoFWl0bQh0EEFTx9oQAFMHdtLSqJVhKzOjpmYhuQ+kjiEkAMM8BzTzQGKOWOxAdws2Nw9U+FERYnEfAJ7TzJQMmh3gAAAAAElFTkSuQmCC',
			'9B4A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WANEQxgaHVqRxUSmiLQytDpMdUASC2gVaQSKBASgirUyBDo6iCC5b9rUqWErMzOzpiG5j9VVpJW1Ea4OAoHmuYYGhoYgiQmA7EBTB3YLmhjEzWjmDVD4URFicR8A393MhcG8N7kAAAAASUVORK5CYII=',
			'5DC7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkNEQxhCHUNDkMQCGkRaGR2AJKpYo2uDAIpYYABIDCSHcF/YtGkrU1etWpmF7L5WsLpWFJshYlOQxQLAYgIByGIiU0BuCXRAFmMNALsZRWygwo+KEIv7AGeazMfPx2IVAAAAAElFTkSuQmCC',
			'7763' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7QkNFQx1CGUIdkEVbGRodHR0dAtDEXBscGkSQxaYwtLIC6QBk90WtmrZ06qqlWUjuY3RgCGB1dGhANo8VKMoKFEE2TwQoii4G4jGiuQWsAt3NAxR+VIRY3AcAluPMg1c1vFgAAAAASUVORK5CYII=',
			'6426' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeElEQVR4nGNYhQEaGAYTpIn7WAMYWhlCGaY6IImJTGGYyujoEBCAJBbQwhDK2hDoIIAs1sDoygAUQ3ZfZNTSpatWZqZmIbkvZIpIK0MrI6p5raKhDlMYHURQxIBuCUAVA7qlldGBAUUvyM2soQEobh6o8KMixOI+ANYfyyGoGwylAAAAAElFTkSuQmCC',
			'F189' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QkMZAhhCGaY6IIkFNDAGMDo6BASgiLEGsDYEOoigiDEA1TnCxMBOCo1aFbUqdFVUGJL7IOocpqLrZQWSWMSw2IHhllB0Nw9U+FERYnEfAC/lyrfRhvIXAAAAAElFTkSuQmCC',
			'C8D7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7WEMYQ1hDGUNDkMREWllbWRsdGkSQxAIaRRpdGwJQxRqA6oBkAJL7olatDFu6KmplFpL7oOpaGVD0gs2bwoBpRwADhlscHbC4GUVsoMKPihCL+wDNOs1HIi7+iwAAAABJRU5ErkJggg==',
			'15C2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7GB1EQxlCHaY6IImxOogAxQMCApDERIFirA2CQBJZr0gIK5AWQXLfyqypS5cC6Sgk9zE6MDS6NjA0OqDoBYu1orpFBCgmMAVVjLUV5BZkMdEQxhCGUMfQkEEQflSEWNwHANVpyYVL9B92AAAAAElFTkSuQmCC',
			'D690' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7QgMYQxhCGVqRxQKmsLYyOjpMdUAWaxVpZG0ICAhAFWtgbQh0EEFyX9TSaWErMyOzpiG5L6BVtJUhBK4Obp5DA6aYI7odWNyCzc0DFX5UhFjcBwDaW82Xc1OHTgAAAABJRU5ErkJggg==',
			'3E35' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7RANEQxmBMABJLGCKSANro6MDispWESAZiCoGVMfQ6OjqgOS+lVFTw1ZNXRkVhew+sDqHBhEM8wKwiAU6IItB3OIQgOw+iJsZpjoMgvCjIsTiPgB1NcvIuo2JTwAAAABJRU5ErkJggg==',
			'903F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7WAMYAhhDGUNDkMREpjCGsDY6OiCrC2hlbWVoCEQTE2l0QKgDO2na1Gkrs6auDM1Cch+rK4o6CATpRTNPAIsd2NwCdTOqeQMUflSEWNwHAIRRyd3gJk3OAAAAAElFTkSuQmCC',
			'CA21' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7WEMYAhhCGVqRxURaGUMYHR2mIosFNLK2sjYEhKKINYg0OjQEwPSCnRS1atrKrJVZS5HdB1bXimpHQINoqMMUNLFGoLoAdLeINDo6oIqxhog0uoYGhAYMgvCjIsTiPgCeTMzPdNrGRgAAAABJRU5ErkJggg==',
			'575B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7QkNEQ11DHUMdkMQCGhgaXRsYHQKwiIkgiQUGMLSyToWrAzspbNqqaUszM0OzkN3XyhAAVI1iHkMrowNIDNm8gFbWBlY0MZEpIg2Mjo4oelkDgCpCGVHcPFDhR0WIxX0AfYnLW0/QkM0AAAAASUVORK5CYII=',
			'B7B3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7QgNEQ11DGUIdkMQCpjA0ujY6OgQgi7UCxRoCGkRQ1bWyNjo0BCC5LzRq1bSloauWZiG5D6guAEkd1DxGB1Z081pZGzDEpog0sKK5JTQAKIbm5oEKPypCLO4DAAvUzygGLj9OAAAAAElFTkSuQmCC',
			'C845' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WEMYQxgaHUMDkMREWllbGVodHZDVBTSKNDpMRRNrAKoLdHR1QHJf1KqVYSszM6OikNwHUsfa6NAggqJXpNEVaKsIuh2Njg4i6G5pdAhAdh/EzQ5THQZB+FERYnEfAPSzzQNhG8/qAAAAAElFTkSuQmCC',
			'8E75' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7WANEQ1lDA0MDkMREpogAyUAHZHUBrZhiYHWNjq4OSO5bGjU1bNXSlVFRSO4Dq5sCpNHNC8AUY3RgdBBBs4MVqBLZfWA3NzBMdRgE4UdFiMV9AEKly2S+h1IoAAAAAElFTkSuQmCC',
			'E99B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGUMdkMQCGlhbGR0dHQJQxEQaXRsCHUSwiAUguS80aunSzMzI0Cwk9wU0MAY6hASimcfQ6IBhHkujI4YYpluwuXmgwo+KEIv7AOLhzLxWLt/SAAAAAElFTkSuQmCC',
			'EC35' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7QkMYQ0EwAEksoIG10bXR0YEBRUykwaEhEEOModHR1QHJfaFR01atmroyKgrJfRB1DiASVS+URLcDVQzkFocAZPdB3Mww1WEQhB8VIRb3AQCKic4+Gs64dwAAAABJRU5ErkJggg==',
			'9A15' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WAMYAhimMIYGIImJTGEMYQhhdEBWF9DK2sqIISbS6DCF0dUByX3Tpk5bmTVtZVQUkvtYXUHqgOYi29wqGoouJgAxz0EExS1gvQHI7mMNEGl0DHWY6jAIwo+KEIv7AK37y3PPC/BWAAAAAElFTkSuQmCC',
			'7CC5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7QkMZQxlCHUMDkEVbWRsdHQIdUFS2ijS4Ngiiik0RaWBtYHR1QHZf1LRVS1etjIpCch+jA0gdQ4MIkl7WBkwxkQaIHchiAQ0gtwQEBKCIgdzsMNVhEIQfFSEW9wEAUQzLvuE683IAAAAASUVORK5CYII=',
			'5A5A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAd0lEQVR4nGNYhQEaGAYTpIn7QkMYAlhDHVqRxQIaGENYGximOqCIsbYCxQICkMQCA0QaXacyOogguS9s2rSVqZmZWdOQ3dcq0ujQEAhTBxUTDQWKhYYg2wFU54qmTmSKSKOjoyOKGCvQXodQRlTzBij8qAixuA8AVxDMTKFPVkIAAAAASUVORK5CYII=',
			'6412' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7WAMYWhmmMEx1QBITAfIZQhgCApDEAloYQhlDGB1EkMUaGF2BehtEkNwXGbV06appq1ZFIbkvZIoIyI5GZDsCWkVDHaYA7UYRA7tlCgOqW0BiAehuZgx1DA0ZBOFHRYjFfQCjucvWq2PWHgAAAABJRU5ErkJggg==',
			'D147' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7QgMYAhgaHUNDkMQCpjAGMLQ6NIggi7WyBjBMRRcD6g10aAhAcl/U0lVRKzOzVmYhuQ+kjrXRoZUBTS9raMAUdDGGRocAFLEpYPc5oLqZNRRdbKDCj4oQi/sAbVfMJHb8PWEAAAAASUVORK5CYII=',
			'5756' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpIn7QkNEQ11DHaY6IIkFNDA0ujYwBARgiDE6CCCJBQYwtLJOZXRAdl/YtFXTlmZmpmYhu6+VIQCkGtk8hlaQvkAHEWQ7WlkbWNHERKaINDA6OqDoZQ0AqghlQHHzQIUfFSEW9wEA9HnLwyjgIXYAAAAASUVORK5CYII=',
			'3A64' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7RAMYAhhCGRoCkMQCpjCGMDo6NCKLMbSytrI2OLSiiE0RaXQFkgFI7lsZNW1l6tRVUVHI7gOpc3R0QDVPNNS1ITA0BEUMZF4AmltEGoFaUcREA0QaHdDcPFDhR0WIxX0AepXOXXc79xoAAAAASUVORK5CYII=',
			'9E99' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7WANEQxlCGaY6IImJTBFpYHR0CAhAEgtoFWlgbQh0EMEtBnbStKlTw1ZmRkWFIbmP1RWoIiRgKrJehlYQL6ABWUwAKMbYEIBiBza3YHPzQIUfFSEW9wEA6xHLJ6H2pS4AAAAASUVORK5CYII=',
			'9E95' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WANEQxlCGUMDkMREpog0MDo6OiCrC2gVaWBtCMQm5uqA5L5pU6eGrcyMjIpCch+rq0gDQ0hAgwiyza0gHqqYAFCMEWgHshjELQ4ByO6DuJlhqsMgCD8qQizuAwAiUcqlnd9JBgAAAABJRU5ErkJggg==',
			'A81B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7GB0YQximMIY6IImxBrC2MoQwOgQgiYlMEWl0BIqJIIkFtALVTYGrAzspaunKsFXTVoZmIbkPTR0YhoaKNDpMQTcPmxim3oBWxhDGUEcUNw9U+FERYnEfAM29y3n8brFyAAAAAElFTkSuQmCC',
			'8358' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WANYQ1hDHaY6IImJTBFpZW1gCAhAEgtoZWh0bWB0EEFRx9DKOhWuDuykpVGrwpZmZk3NQnIfSB3QBAzzHBoCUcyD2BGIZodIK6OjA4pekJsZQhlQ3DxQ4UdFiMV9AHI1zG5maeZIAAAAAElFTkSuQmCC',
			'08B1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDGVqRxVgDWFtZGx2mIouJTBFpdG0ICEUWC2gFq4PpBTspaunKsKWhq5Yiuw9NHVQMbF4rFjuwuQVFDOrm0IBBEH5UhFjcBwBwT8yAK6E2ugAAAABJRU5ErkJggg==',
			'89E0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDHVqRxUSmsLayNjBMdUASC2gVaXRtYAgIQFEHEmN0EEFy39KopUtTQ1dmTUNyn8gUxkAkdVDzGBoxxViw2IHpFmxuHqjwoyLE4j4A/6zMCnoCkaoAAAAASUVORK5CYII=',
			'AABC' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7GB0YAlhDGaYGIImxBjCGsDY6BIggiYlMYW1lbQh0YEESC2gVaXRtdHRAdl/U0mkrU0NXZiG7D00dGIaGioa6As1jQDcPqx2obgGLobl5oMKPihCL+wDD3809uduKUAAAAABJRU5ErkJggg==',
			'3E05' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7RANEQxmmMIYGIIkFTBFpYAhldEBR2SrSwOjoiCoGVMfaEOjqgOS+lVFTw5auioyKQnYfWF1AgwiaedjEQHaIYLiFIQDZfRA3M0x1GAThR0WIxX0AG0XKs5Zx8e4AAAAASUVORK5CYII='        
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