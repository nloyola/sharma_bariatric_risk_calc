<?php
/**
 * Template Name: Sharma Bariatric Risk Calculator
 *
 * Description: Calculates rish of death based on user's input.
 *
 */

get_header(); ?>
<div id="primary" class="site-content">
   <div id="content" role="main">

<?php if (have_posts()) : while (have_posts()) : the_post();?>
<?php if ( has_post_thumbnail() ) : ?>
   <div class="entry-page-image">
<?php the_post_thumbnail(); ?>
   </div><!-- .entry-page-image -->
<?php endif; ?>

   <div class="entry-content">
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
         } else {
            echo renderInvalidInput('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
         }
      }
   } else {
      get_template_part( 'content', 'page' );
   }

?>

<?php endwhile; endif; ?>

</div>
</div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar( 'front' ); ?>
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
   $result = 0;
   if ($points < 20) {
      $result = 0.2;
   } else if (($points >= 20) && ($points < 40)) {
      $result = 0.9;
   } else if (($points >= 40) && ($points < 60)) {
      $result = 2.0;
   } else if ($points >= 60) {
      $result = 5.2;
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
<table style="width:75%;" class="form-table">
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
<th scope="row">Smoker</th>
<td>$is_smoker</td>
</tr>
</tbody>
</table>
<p><strong>Total score: $points points</strong></p>
<p><strong>Risk of dying in the next 10 years is $risk%.</strong></p>
<p><a href=""><button type="button">Reset</button></a>
HTML_RESULT;
}

?>
