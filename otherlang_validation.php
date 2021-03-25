<?php 
$lang = get_langcode($_SESSION['sess_lang']);
if($lang != 'en') { ?>
<script>

    $.extend( $.validator.messages, {

    required: "<?php echo addslashes($langage_lbl['LBL_FEILD_REQUIRD']);?>",

        remote: "<?php echo addslashes($langage_lbl['LBL_FEILD_REMOTE']);?>",

        email: "<?php echo addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR']);?>",

        url: "<?php echo addslashes($langage_lbl['LBL_FIELD_URL']);?>",

        date: "<?php echo addslashes($langage_lbl['LBL_FIELD_DATE']);?>",

        dateISO: "<?php echo addslashes($langage_lbl['LBL_FIELD_DATE']);?> (ISO).",

        number: "<?php echo addslashes($langage_lbl['LBL_FIELD_NUMBER']);?>",

        digits: "<?php echo addslashes($langage_lbl['LBL_FIELD_DIGIT']);?>",

        equalTo: "<?php echo addslashes($langage_lbl['LBL_FIELD_SAME_VALUE']);?>",

        maxlength: $.validator.format( "<?php echo addslashes($langage_lbl['LBL_FIELD_MAXLENGTH']);?>" ),

        minlength: $.validator.format( "<?php echo addslashes($langage_lbl['LBL_FIELD_MINLENGTH']);?>" ),

        rangelength: $.validator.format( "<?php echo addslashes($langage_lbl['LBL_FIELD_RANGELENGTH']);?>" ),

        range: $.validator.format( "<?php echo addslashes($langage_lbl['LBL_FIELD_RANGE']);?>" ),

        max: $.validator.format( "<?php echo addslashes($langage_lbl['LBL_FIELD_MAX']);?>" ),

        min: $.validator.format( "<?php echo addslashes($langage_lbl['LBL_FIELD_MIN']);?>" ),

        step: $.validator.format( "<?php echo addslashes($langage_lbl['LBL_FIELD_STEP']);?>" )

    } );

</script>

<? } ?>