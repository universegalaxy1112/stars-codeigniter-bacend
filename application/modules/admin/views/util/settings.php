<?php echo $form->messages(); ?>

<div class="row">

    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-body">
                <?php echo $form->open(); ?>
                <?php echo $form->bs3_textarea('About', 'about', $constant['about']); ?>
                <?php echo $form->bs3_textarea('Mnemonic', 'mnemonic', $constant['mnemonic']); ?>

                <div class="col-md-12"
                     style="margin-top: 20px; margin-bottom: 20px; padding-top: 10px; border-color: lightgrey; border-width: 1px; border-style: solid">
                    <div class="col-md-12">
                        <h4>QuestionPalooza Date Setting</h4>
                    </div>

                    <div class="col-md-12" style="margin-top: 10px">
                        <div class="col-md-6 col-sm-9 col-xs-12">
                            <?php echo $form->bs3_text('Start Day of Month', 'q_palooza_day_of_month', $constant['q_palooza_day_of_month']); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-9">
                            <?php echo $form->bs3_text('Before Days', 'q_palooza_before_days', $constant['q_palooza_before_days']); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-9">
                            <?php echo $form->bs3_text('Active Days', 'q_palooza_active_days', $constant['q_palooza_active_days']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-12"
                     style="margin-top: 20px; margin-bottom: 20px; padding-top: 10px; border-color: lightgrey; border-width: 1px; border-style: solid">
                    <div class="col-md-12">
                        <h4>TriadPalooza Date Setting</h4>
                    </div>

                    <div class="col-md-12" style="margin-top: 10px">
                        <div class="col-md-6 col-sm-9 col-xs-12">
                            <?php echo $form->bs3_text('Start Day of Month', 't_palooza_day_of_month', $constant['t_palooza_day_of_month']); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-9">
                            <?php echo $form->bs3_text('Before Days', 't_palooza_before_days', $constant['t_palooza_before_days']); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-9">
                            <?php echo $form->bs3_text('Active Days', 't_palooza_active_days', $constant['t_palooza_active_days']); ?>
                        </div>
                    </div>
                </div>

                <?php echo form_checkbox('review_mode', '1', $constant['review_code'] == '4.3.01' ? TRUE : FALSE, 'id="review_mode"'); ?>
                <?php echo form_label('Apple Review Mode ( Users have to signup )', 'review_mode'); ?>

                <div class="col-md-12" style="padding-top: 20px">
                    <?php echo $form->bs3_submit(); ?>
                </div>

                <?php echo $form->close(); ?>
            </div>
        </div>
    </div>

</div>

<!--<link rel="stylesheet" href="<?php /*echo base_url(); */ ?>assets/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="<?php /*echo base_url(); */ ?>assets/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>

<script>
    $(function () {
        //bootstrap WYSIHTML5 - text editor
        $('textarea').wysihtml5()
    })
</script>

<style>
    .wysihtml5-sandbox {
        height: 500px;
    }
</style>-->