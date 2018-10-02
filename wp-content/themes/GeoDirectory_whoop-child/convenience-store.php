<?php /* Template Name: All convenience stores */ ?>
<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline">
            <?php the_title(); ?>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'7-eleven';?>">7-eleven</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'108-ช็อป';?>">108-ช็อป</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ซีพี-เฟรชมาร์ท';?>">ซีพี-เฟรชมาร์ท</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'จิ๊ฟฟี่';?>">จิ๊ฟฟี่</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ท็อปซุปเปอร์มาเก็ต';?>">ท็อปซุปเปอร์มาเก็ต</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'บิ๊กซี';?>">บิ๊กซี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'วิลล่า-มาร์เก็ต';?>">วิลล่า-มาร์เก็ต</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'เทสโก้โลตัส';?>">เทสโก้โลตัส</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'แฟมิลี่มาร์ท';?>">แฟมิลี่มาร์ท</a>
            </li>
          </ul>
        </section>
        <?php // end article section ?>
      </article>
      <?php endwhile; else : ?>
      <article id="post-not-found" class="hentry cf">
        <header class="article-header">
          <h1>
            <?php _e( 'Oops, Post Not Found!', GEODIRECTORY_FRAMEWORK ); ?>
          </h1>
        </header>
        <section class="entry-content">
          <p>
            <?php _e( 'Uh Oh. Something is missing. Try double checking things.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </section>
        <footer class="article-footer">
          <p>
            <?php _e( 'This is the error message in the page.php template.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </footer>
      </article>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php get_footer(); ?>
