<div id="ride-type" style="display:block;">
    <?php if (isset($FEMALE_RIDE_REQ_ENABLE) && $FEMALE_RIDE_REQ_ENABLE == "Yes") { ?>
        <span class="auto_assign001">
            <input type="checkbox" name="eFemaleDriverRequest" id="eFemaleDriverRequest" value="Yes" <?php if ($eFemaleDriverRequest == 'Yes') echo 'checked'; ?>>
            <p>Ladies Only Ride?</p>
        </span>
    <?php } if (isset($HANDICAP_ACCESSIBILITY_OPTION) && $HANDICAP_ACCESSIBILITY_OPTION == "Yes") { ?>
        <span class="auto_assign001">
            <input type="checkbox" name="eHandiCapAccessibility" id="eHandiCapAccessibility" value="Yes" <?php if ($eHandiCapAccessibility == 'Yes') echo 'checked'; ?>>
            <p>Prefer Handicap Accessibility?</p>
        </span>
        <?php
    }
    if (isset($WHEEL_CHAIR_ACCESSIBILITY_OPTION) && $WHEEL_CHAIR_ACCESSIBILITY_OPTION == "Yes") {
        $html .= '<span class="auto_assign001">
            <input type="checkbox" name="eWheelChairAvailable" id="eWheelChairAvailable" value="Yes" ' . $checkedwheel . '>
            <p>Wheel Chair available?</p>
        </span>';
        ?>
        <?php
    }
    if ($PACKAGE_TYPE == "SHARK") {
        require_once('../include/include_webservice_sharkfeatures.php');
        echo getAccesiblityOption($eChildSeatAvailable, $eWheelChairAvailable);
    }
    ?>
</div>