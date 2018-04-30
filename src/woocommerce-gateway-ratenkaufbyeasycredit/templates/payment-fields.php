<?php $id = esc_attr($easyCredit->id); ?>
<?php if ($easyCreditError) : ?>

    <div id="easycredit-error">
        <?php echo $easyCreditError; ?>
    </div>

<?php else: ?>
    <fieldset id="wc-<?php echo id; ?>-form" class='wc-payment-form'>
        <p class="form-row">
            <label for="<?php echo $id; ?>-prefix"><?php echo esc_html__( 'Prefix', 'woocommerce-gateway-ratenkaufbyeasycredit' ); ?><span class="required">*</span></label>
            <select id="<?php echo $id; ?>-prefix" class="input-select <?php echo $id; ?>-prefix validate-required" name="<?php echo $id; ?>-prefix">
                <option name="" disabled selected><?php echo esc_html__( 'Please select a title', 'woocommerce-gateway-ratenkaufbyeasycredit' ); ?></option>
                <option name="Herr"><?php echo esc_html__( 'Mr.', 'woocommerce-gateway-ratenkaufbyeasycredit' ); ?></option>
                <option name="Frau"><?php echo esc_html__( 'Ms/Mrs.', 'woocommerce-gateway-ratenkaufbyeasycredit' ); ?></option>
            </select>
        </p>

        <p class="form-row">
            <label for="<?php echo $id; ?>-agreement" class="<?php echo $id; ?>-agreement">
                    <input type="checkbox" name="<?php echo $id; ?>-agreement" class="validate-required" id="<?php echo $id; ?>-agreement" />
                    <span><?php echo $easyCreditAgreement; ?></span>
            </label>
        </p>
    </fieldset>
<?php endif; ?>
