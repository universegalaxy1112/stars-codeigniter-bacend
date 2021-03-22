<?php if (!empty($crud_note)) echo "<p>$crud_note</p>"; ?>

<?php if (isset($form)): ?>
    <div class="row">
        <?php echo $form->open(); ?>

        <?php if (isset($vignettes) && isset($selected_vignette_id)): ?>
            <div class="col-md-4 form-group">
                <span class="input-group date">
                <div class="input-group-addon">
                    <label>Vignette : </label>
                </div>
                <select id="vignette_selector" class="form-control select2">
                    <?php foreach ($vignettes as $vignette): ?>
                        <?php if ($vignette->id == $selected_vignette_id): ?>
                            <option id="<?php echo $vignette->id; ?>" value="<?php echo $vignette->id; ?>"
                                    selected="selected"><?php echo $vignette->title; ?></option>
                        <?php else: ?>
                            <option id="<?php echo $vignette->id; ?>"
                                    value="<?php echo $vignette->id; ?>"><?php echo $vignette->title; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                </span>
                <input hidden type="text" id="vignette_id" name="vignette_id" value="<?php echo $selected_vignette_id; ?>">
            </div>
        <?php endif; ?>

        <?php if (isset($parts) && isset($selected_part_id)): ?>
            <div class="col-md-4 form-group">
                <span class="input-group date">
                <div class="input-group-addon">
                    <label>Part: </label>
                </div>
                <select id="part_selector" class="form-control select2">
                    <?php foreach ($parts as $part): ?>
                        <?php if ($part->id == $selected_part_id): ?>
                            <option id="<?php echo $part->id; ?>" value="<?php echo $part->id; ?>"
                                    selected="selected"><?php echo $part->title; ?></option>
                        <?php else: ?>
                            <option id="<?php echo $part->id; ?>"
                                    value="<?php echo $part->id; ?>"><?php echo $part->title; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                </span>
                <input hidden type="text" id="part_id" name="part_id" value="<?php echo $selected_part_id; ?>">
            </div>
        <?php endif; ?>

        <?php if (isset($categories) && isset($selected_category_id)): ?>
            <div class="col-md-4 form-group">
                <span class="input-group date">
                <div class="input-group-addon">
                    <label>Category: </label>
                </div>
                <select id="category_selector" class="form-control select2" style="width: 100%;">
                    <?php foreach ($categories as $category): ?>
                        <?php if ($category->id == $selected_category_id): ?>
                            <option id="<?php echo $category->id; ?>" value="<?php echo $category->id; ?>"
                                    selected="selected"><?php echo $category->title; ?></option>
                        <?php else: ?>
                            <option id="<?php echo $category->id; ?>"
                                    value="<?php echo $category->id; ?>"><?php echo $category->title; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                </span>
                <input hidden type="text" id="category_id" name="category_id" value="<?php echo $selected_category_id; ?>">
            </div>
        <?php endif; ?>

        <?php if (isset($quizzes) && isset($selected_quiz_id)): ?>
            <div class="col-md-4 form-group">
                <span class="input-group date">
                <div class="input-group-addon">
                    <label>Quiz: </label>
                </div>
                <select id="quiz_selector" class="form-control select2" style="width: 100%;">
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php if ($quiz->id == $selected_quiz_id): ?>
                            <option id="<?php echo $quiz->id; ?>" value="<?php echo $quiz->id; ?>"
                                    selected="selected"><?php echo $quiz->title; ?></option>
                        <?php else: ?>
                            <option id="<?php echo $quiz->id; ?>"
                                    value="<?php echo $quiz->id; ?>"><?php echo $quiz->title; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                </span>
                <input hidden type="text" id="quiz_id" name="quiz_id" value="<?php echo $selected_quiz_id; ?>">
            </div>
        <?php endif; ?>

        <?php if (isset($exams) && isset($selected_exam_id)): ?>
            <div class="col-md-4 form-group">
                <span class="input-group date">
                <div class="input-group-addon">
                    <label>Exam: </label>
                </div>
                <select id="exam_selector" class="form-control select2" style="width: 100%;">
                    <?php foreach ($exams as $exam): ?>
                        <?php if ($exam->id == $selected_exam_id): ?>
                            <option id="<?php echo $exam->id; ?>" value="<?php echo $exam->id; ?>"
                                    selected="selected"><?php echo $exam->title; ?></option>
                        <?php else: ?>
                            <option id="<?php echo $exam->id; ?>"
                                    value="<?php echo $exam->id; ?>"><?php echo $exam->title; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                </span>
                <input hidden type="text" id="exam_id" name="exam_id" value="<?php echo $selected_exam_id; ?>">
            </div>
        <?php endif; ?>

        <div class="col-md-6" hidden>
            <?php echo $form->bs3_submit('Submit', 'btn btn-primary', array('id' => 'btn_submit')); ?>
        </div>
        <?php echo $form->close(); ?>
    </div>
<?php endif; ?>


<?php if (!empty($crud_output)) echo $crud_output; ?>

<style>
    ul {
        list-style-type: none;
    }

    .input-group-addon label {
        margin-bottom: -5px;
    }
</style>

<script>
    $(function () {
        $('#vignette_selector').on('change', function () {
            $('#vignette_id').val($(this).children(":selected").attr("id"));
            $('#btn_submit').trigger('click');
        });

        $('#part_selector').on('change', function () {
            $('#part_id').val($(this).children(":selected").attr("id"));
            $('#btn_submit').trigger('click');
        });

        $('#category_selector').on('change', function () {
            $('#category_id').val($(this).children(":selected").attr("id"));
            $('#btn_submit').trigger('click');
        });

        $('#quiz_selector').on('change', function () {
            $('#quiz_id').val($(this).children(":selected").attr("id"));
            $('#btn_submit').trigger('click');
        });
    });
</script>
