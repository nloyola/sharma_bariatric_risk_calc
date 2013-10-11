<?php
/**
 * Template Name: Sharma Bariatric Risk Calculator
 *
 * Description: Calculates rish of death based on user's input.
 *
 * This file should be placed in:
 *
 *   <wordpress_dir>/wp-content/themes/<current_theme>/page_templates/
 */

get_header(); ?>


<div id="lilwrapper">

   <div id="content">

   <h2>Bariatric Mortality Risk Calculator</h2>

<?php

   if (isset($_POST) && array_key_exists('save', $_POST)) {
      if ($_POST['save'] == 'Submit') {
         //debugVar($_POST);

         $age          = $_POST['age'];
         $sex          = $_POST['sex'];
         $has_diabetes = $_POST['has_diabetes'];
         $is_smoker    = $_POST['is_smoker'];

         if (validInput($age, $sex, $has_diabetes, $is_smoker)) {
            $has_diabetes = ($_POST['has_diabetes'] == 'yes');
            $is_smoker    = ($_POST['is_smoker'] == 'yes');

            $points = mortalityRiskCalc($age, $sex, $has_diabetes, $is_smoker);

            echo renderResults($age, ucfirst($sex),
                               $has_diabetes ? "Yes" : "No",
                               $is_smoker ? "Yes" : "No",
                               $points,
                               tenYearMortalityRisk($points));
            echo calcExplanation();
         } else {
            echo renderInvalidInput('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
         }
      }
   } else {
      echo renderForm();
      echo calcExplanation();
   }

?>

   </div>
   <?php include("left.php"); ?>
   </div>

   <div id="wrapper">
   <?php include("right.php"); ?>
   </div>

   </div>

<?php get_footer(); ?>

<?php

function debugVar($var) {
   echo "<pre>\n";
   var_dump($var);
   echo "</pre>\n";
}

/*
 * Here is the info for the calculator:
 *
 * Age: 1 point for every year older than 18
 * Male sex: 6 points
 * Type 2 Diabetes yes: 10 points
 * Current smoker yes: 6 points
 *
 * Scoring of 10 year mortality risk:
 *
 * <20 = 0.2%
 * 20-39 = 0.9%
 * 40-59 = 2%
 * >=60 = 5.2%
 *
 * Example
 *
 * my age is 54 = 36 points
 * male sex = 6 points
 * type 2 diabetes no = 0 points
 * current smoker no = 0 points
 *
 * Total score: 42 points
 *
 * My risk of dying in the next 10 years is 2% or 1 in 50
 */

function validInput($age, $sex, $has_diabetes, $is_smoker) {
   return is_numeric($age)
      && (($age > 0) && ($age < 120))
      && (($sex == 'male') || ($sex == 'female'))
      && (($has_diabetes == 'yes') || ($has_diabetes == 'no'))
      && (($is_smoker == 'yes') || ($is_smoker == 'no'));
}

function mortalityRiskCalc($age, $sex, $has_diabetes, $is_smoker) {
   $points = 0;
   if ($age > 18) { $points += $age - 18; }
   if ($sex == "male") { $points += 6; }
   if ($has_diabetes) { $points += 10; }
   if ($is_smoker) { $points += 6; }

   return $points;
}

function tenYearMortalityRisk($points) {
   $result = array(0, '');
   if ($points < 20) {
      $result = '0.2% or 1 in 500';
   } else if (($points >= 20) && ($points < 40)) {
      $result = '0.9% or less than 1 in 100';
   } else if (($points >= 40) && ($points < 60)) {
      $result = '2% or 1 in 50';
   } else if ($points >= 60) {
      $result = '5.2% or greater than 1 in 20';
   }
   return $result;
}


function renderInvalidInput($link) {
   return <<<HTML_INVALID_INPUT
<h3>Invalid</h3>
<p>Invalid information entered. Please submit your information again.</p>
<p><a href="$link">Back to previous page</a>.</p>
HTML_INVALID_INPUT;
}

function renderResults($age, $sex, $has_diabetes, $is_smoker, $points, $risk) {
   return <<<HTML_RESULT
<h3>Results</h3>
<div class="sharma-table">
<table style="width:75%;">
<tbody>
<tr valign="top">
<th scope="row">Age</th>
<td>$age</td>
</tr>
<tr valign="top">
<th scope="row">Sex</th>
<td>$sex</td>
</tr>
<tr valign="top">
<th scope="row">Type 2 Diabetes</th>
<td>$has_diabetes</td>
</tr>
<tr valign="top">
<th scope="row">Current smoker</th>
<td>$is_smoker</td>
</tr>
</tbody>
</table>
</div>
<p><strong>Total score: $points points out of 107</strong></p>
<p><strong>Risk of dying in the next 10 years is $risk.</strong></p>
<p><a href=""><button type="button">Reset</button></a>
HTML_RESULT;
}

function renderForm() {
   return <<<HTML_FORM
<p>The following parameters predicts the risk in men and women 18-65 years old meeting current criteria for
bariatric surgery (BMI &ge; 35 kg/m<sup>2</sup> or BMI  &ge; 30 kg/m<sup>2</sup> and a weight-related
illness<sup>*</sup>)</p>

<div class="sharma-table">
<form action="" method="POST">
<table>
<tbody>
<tr valign="top">
<th scope="row"><label for="age">Age</label></th>
<td><input type="text" maxlength="3" name="age" size="10" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="sex">Sex</label></th>
<td><input type="radio" name="sex" value="male" /> Male<br>
<input type="radio" name="sex" value="female" /> Female</td>
</tr>
<tr valign="top">
<th scope="row"><label for="has_diabetes">Type 2 diabetes</label></th>
<td><input type="radio" name="has_diabetes" value="yes" /> Yes<br>
<input type="radio" name="has_diabetes" value="no" /> No</td>
</tr>
<tr valign="top">
<th scope="row"><label for="is_smoker">Current smoker</label></th>
<td><input type="radio" name="is_smoker" value="yes" /> Yes<br>
<input type="radio" name="is_smoker" value="no" /> No</td>
</tr>
<tr valign="top">
<td></td>
<td><input class="button-primary" type="submit" name="save" value="Submit" />
<input class="button-secondary" type="submit" name="reset" value="Reset" /></td>
</tr>
</tbody>
</table>
</form>
</div>
HTML_FORM;
}

function calcExplanation() {
   return <<<HTML_CALC_EXPLANATION
<p>This simple 4-variable clinical prediction rule for mortality risk in men and women eligible for
bariatric surgery is based on an analysis of rates and correlates of all-cause mortality in over
15,000 participants in the UK General Practice Research Database (UK-GPRD) between January 1, 1988,
through December 31, 1998.</p>

<div class="disclaimers" style="font-size:0.9em;">
<p><sup>*</sup>Weight-related illnesses used to define eligibility for surgery: hypertension, dyslipidemia, heart
failure, type 2 diabetes mellitus, sleep apnea, osteoarthritis, coronary artery disease, and
cerebrovascular disease.</p>

<p><i>Reference: Padwal RS, Klarenback SW, Wang X, Sharma AM, Karmali S, Birch DW, Majumdar SR. A simple
prediction rule for all-cause mortality in a cohort eligible for bariatric surgery. JAMA Surgery,
October 16, 2013</i></p>

<p>Disclaimer: This prediction rule is for information only. It should not be construed as health
advice. Your risk and treatment decisions should be discussed with your health professional.</p>
</div>
HTML_CALC_EXPLANATION;
}

?>
