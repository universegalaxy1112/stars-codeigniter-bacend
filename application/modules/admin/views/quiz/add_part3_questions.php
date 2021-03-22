<?php echo $form->messages(); ?>

<div class="row">

	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-body">
				<?php echo $form->open(); ?>

                    <?php echo $form->bs3_text('Title', 'title'); ?>

                    <span>
                        <div class="col-md-6" style="margin-left: -2ex;">
                            <?php echo $form->bs3_textarea('Story', 'story'); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $form->bs3_textarea('Study', 'study'); ?>
                        </div>
                    </span>
                    <?php echo $form->bs3_upload('Image', 'image'); ?>

                    <table class="col-md-12" cellspacing="20">
                        <tr>
                            <th width="70px"></th>
                            <th style="text-align: center; height: 30px; color: #fff;">Question1</th>
                            <th style="text-align: center; height: 30px; color: #fff;">Question2</th>
                            <th style="text-align: center; height: 30px; color: #fff;">Question3</th>
                        </tr>
                        <tr>
                            <td style="vertical-align: text-top">Question : </td>
                            <td><textarea name="question1" class="form-control" id="question1" rows="5" cols="40"></textarea></td>
                            <td><textarea name="question2" class="form-control" id="question2" rows="5" cols="40"></textarea></td>
                            <td><textarea name="question3" class="form-control" id="question3" rows="5" cols="40"></textarea></td>
                        </tr>
                        <tr>
                            <td>option_a : </td>
                            <td><input class="form-control" name="option_a1" id="option_a1" type="text" value=""></td>
                            <td><input class="form-control" name="option_a2" id="option_a2" type="text" value=""></td>
                            <td><input class="form-control" name="option_a3" id="option_a3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>option_b : </td>
                            <td><input class="form-control" name="option_b1" id="option_b1" type="text" value=""></td>
                            <td><input class="form-control" name="option_b2" id="option_b2" type="text" value=""></td>
                            <td><input class="form-control" name="option_b3" id="option_b3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>option_c : </td>
                            <td><input class="form-control" name="option_c1" id="option_c1" type="text" value=""></td>
                            <td><input class="form-control" name="option_c2" id="option_c2" type="text" value=""></td>
                            <td><input class="form-control" name="option_c3" id="option_c3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>option_d : </td>
                            <td><input class="form-control" name="option_d1" id="option_d1" type="text" value=""></td>
                            <td><input class="form-control" name="option_d2" id="option_d2" type="text" value=""></td>
                            <td><input class="form-control" name="option_d3" id="option_d3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>option_e : </td>
                            <td><input class="form-control" name="option_e1" id="option_e1" type="text" value=""></td>
                            <td><input class="form-control" name="option_e2" id="option_e2" type="text" value=""></td>
                            <td><input class="form-control" name="option_e3" id="option_e3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>option_f : </td>
                            <td><input class="form-control" name="option_f1" id="option_f1" type="text" value=""></td>
                            <td><input class="form-control" name="option_f2" id="option_f2" type="text" value=""></td>
                            <td><input class="form-control" name="option_f3" id="option_f3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>option_g : </td>
                            <td><input class="form-control" name="option_g1" id="option_g1" type="text" value=""></td>
                            <td><input class="form-control" name="option_g2" id="option_g2" type="text" value=""></td>
                            <td><input class="form-control" name="option_g3" id="option_g3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>option_h : </td>
                            <td><input class="form-control" name="option_h1" id="option_h1" type="text" value=""></td>
                            <td><input class="form-control" name="option_h2" id="option_h2" type="text" value=""></td>
                            <td><input class="form-control" name="option_h3" id="option_h3" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td>answer : </td>
                            <td><input class="form-control" name="answer1" id="answer1" type="text" value=""></td>
                            <td><input class="form-control" name="answer2" id="answer2" type="text" value=""></td>
                            <td><input class="form-control" name="answer3" id="answer3" type="text" value=""></td>
                        </tr>
                    </table>

                    <div class="col-md-12">
                        <div class="float-right" style="float: right; margin-top: 10px;"><?php echo $form->bs3_submit(); ?></div>
                    </div>
				<?php echo $form->close(); ?>
			</div>
		</div>
	</div>
	
</div>

<style>
    table {
        border-spacing: 20px;
        background: #367fa9;
    }
    td {
        background: #FFFFFF;
        padding: 10px;
    }
</style>