<?php
/* Smarty version 3.1.33, created on 2019-10-29 10:17:13
  from '/Users/meizj/Documents/timeline/gradutedesign/PHPVulFinder/views/index.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5db7a129c49631_09719532',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ea7e55aa5b1fa56531522497ccc924f64e130927' => 
    array (
      0 => '/Users/meizj/Documents/timeline/gradutedesign/PHPVulFinder/views/index.html',
      1 => 1572315203,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5db7a129c49631_09719532 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <?php echo '<script'; ?>
 src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"><?php echo '</script'; ?>
>
    <title>Document</title>
</head>
<body style="background-color: #B3C0D1">

<div class="container-fluid " >
    <div class="row align-items-start jumbotron">
        <h1 class="display-5">PHPVulFinder</h1>
    </div>
    <div class="row align-items-center input-group input-group-lg ">

            <div class="input-group-prepend">
                <span class="input-group-text" id="inputGroup-sizing-lg">Path</span>
            </div>
            <input  id="pathData" type="text" class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm" />
            <!--<button id="pathSubmit" type="button" class="btn btn-secondary" onclick="submit()">Submit</button>-->
            <br/>


            <button id="pathSubmit" onclick="submit()" class="btn btn-primary btn-lg " type="submit">Button</button>

    </div>

    <div class="row align-items-end ">
        <div class="col" id="result">
            result
        </div>
    </div>

</div>
<?php echo '<script'; ?>

        src="https://code.jquery.com/jquery-3.4.1.js"
        integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
        crossorigin="anonymous"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>

    function submit() {

        formData = document.getElementById("pathData").value;

        $.ajax({
            type: 'POST',
            url: '/main.php',
            data: {
                'path':formData
            }
        }).done(function(result){
            document.getElementById("result").innerHTML = result;
        });

    }


<?php echo '</script'; ?>
>

</body>
</html><?php }
}
