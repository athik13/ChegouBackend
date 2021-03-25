<?php
  $sql="select count(hd.iDriverId) as Total from home_driver as hd LEFT JOIN company as c on hd.iCompanyId = c.iCompanyId where hd.eStatus='Active'";
  $count_driver = $obj->MySQLSelect($sql);
  
  if($count_driver[0]['Total'] > 4){
    $ssql = " order by rand()"; 
  }else{
    $ssql = " order by hd.iDisplayOrder";
  }
  $sql="select hd.iCompanyId,hd.vImage,c.vCompany,c.vAvgRating from home_driver as hd LEFT JOIN company as c on hd.iCompanyId = c.iCompanyId where hd.eStatus='Active' $ssql limit 4";
  $db_home_drv=$obj->MySQLSelect($sql);
  
foreach ($db_home_drv as $key => $value) {
  $DataArray[$value['iCompanyId']] = $value;
  $sql1 = "SELECT cuisineId,iCompanyId FROM `company_cuisine` WHERE iCompanyId = '" . $value['iCompanyId'] . "' order by rand() limit 2";
  $db_cusinedata = $obj->MySQLSelect($sql1);
  foreach ($db_cusinedata as $k => $val) {
    $selectcuisine_sql = "SELECT cuisineName_" . $_SESSION['sess_lang'] . " FROM cuisine WHERE eStatus = 'Active' AND cuisineId = '".$val['cuisineId']."'";
    $db_cuisine = $obj->MySQLSelect($selectcuisine_sql);
    $cusineselecteddata[$val['iCompanyId']][] = $db_cuisine[0]["cuisineName_".$_SESSION['sess_lang']];
  }
}
foreach ($cusineselecteddata as $ke => $v) {
  $cuisines[$ke]  = implode(",", $v);
}
$RestaurantData = array();
foreach ($DataArray as $k => $v) {
  foreach ($cuisines as $cuisinekey => $cuisineval) {
    if($cuisinekey == $k){
      $v['cusines'] = $cuisineval;
    }
  }
  $RestaurantData[] = $v;
}

?>
<div id="home-page">

   <!-- body -->
  <div class="why-choose-us-part">
    <div class="why-choose-us-part-inner">
      <div class="why-choose-us-part-left">
        <?if(!empty($data[0]['FirstSectionLeftImage'])) { ?>
          <img src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['FirstSectionLeftImage'];?>" alt="" />
        <?php } else { ?>
          <img src="assets/img/food-home/why-choose-img.png" alt="" />
        <?php } ?>
      </div>
      <div class="why-choose-us-part-right">
        <h2>
          <?if(!empty($data[0]['FirstSectionHeading'])) { ?>
            <?= $data[0]['FirstSectionHeading'];?>
          <?php } else { ?>
            why choose us
          <?php } ?>
        </h2>
        <ul>
          <li>
            <b><img src="assets/img/food-home/why-choose-us-icon1.jpg" alt="" /></b> 
            <span>
              <strong>
                <? if(!empty( $data[0]['FirstParaTitle'])) {
                  echo $data[0]['FirstParaTitle'];
                } else { 
                  echo 'Fresh Food Made With Honest Ingredients';
                }?>  
              </strong>
              <? if(!empty( $data[0]['FirstParaContent'])) {
                  echo $data[0]['FirstParaContent'];
                } else { 
                  echo 'To Present You Nutritionally Balanced Luscious Delicacies Made with the choicest of ingredients, our chefs will create your mouthwatering menu that is nutritionally balanced and tasty, full of the goodness of vegetables and fruits with a taste that leaves your taste buds tingling long after you have eaten the meal.';
                }?>
            </span>
          </li>
          <li>
            <b><img src="assets/img/food-home/why-choose-us-icon2.jpg" alt="" /></b> 
            <span>
              <strong>
                <? if(!empty( $data[0]['SecondParaTitle'])) {
                  echo $data[0]['SecondParaTitle'];
                } else { 
                  echo 'Convenient, Cost Effective And Delivered With Care';
                }?>
                </strong>
                <? if(!empty( $data[0]['SecondParaContent'])) {
                  echo $data[0]['SecondParaContent'];
                } else { 
                  echo 'The best food, from the best eateries, with the best prices, delivered specially for you ensuring that you get the satisfaction of eating a gourmet meal in the comforts of your home or venue. No menu is difficult for us to prepare – just say the word and it will be delivered on time, just for you.';
                }?>
            </span>
          </li>
          <li>
            <b>
              <img src="assets/img/food-home/why-choose-us-icon3.jpg" alt="" />
            </b>
            <span>
              <strong>
              <? if(!empty( $data[0]['ThirdParaTitle'])) {
                  echo $data[0]['ThirdParaTitle'];
                } else { 
                  echo 'Enjoy The Delights Of A Digitalised Global Cuisine App';
                }?>
                </strong>
                <? if(!empty( $data[0]['ThirdParaContent'])) {
                  echo $data[0]['ThirdParaContent'];
                } else { 
                  echo 'The digital world is moving so fast that you can now have a gourmet meal delivered to your doorstep using this phenomenal technology. No more waiting in restaurant queues, spending hours browsing the menu or an hour before your first course gets to your table. Enchiladas, escargots, chicken vindaloo, pizza, tiramisu, Thai green curry with lemon scented rice, or just plain fish and chips, we have it all for you. What are you waiting for? Your mouth is already watering just reading those names – tap the app now!';
                }?>              
            </span>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <!-- -->
  <div class="services-part">
    <div class="services-part-inner">
      <ul>
        <li>
          <b>
              <?if(!empty($data[0]['MidFirstImage'])) { ?>
                <img src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['MidFirstImage'];?>" alt="" />
              <?php } else { ?>
                 <img alt="" src="assets/img/food-home/no-minimum-order.png">
              <?php } ?>
          </b> 
          <strong>
            <?if(!empty($data[0]['MidFirstTitle'])) { 
              echo $data[0]['MidFirstTitle'];
            } else { 
              echo 'No Minimum Order';
            } ?>
          </strong>

          <?if(!empty($data[0]['MidFirstContent'])) { 
              echo $data[0]['MidFirstContent'];
          } else { 
            echo '<p>Order in for yourself or for the group, with no restrictions on order value</p>';
          } ?>

        </li>

        <li>

          <b>
              <?if(!empty($data[0]['MidSecImage'])) { ?>
                <img src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['MidSecImage'];?>" alt="" />
              <?php } else { ?>
                 <img alt="" src="assets/img/food-home/live-order-tracking.png">
              <?php } ?>
          </b> 
          <strong>
            <?if(!empty($data[0]['MidSecTitle'])) { 
              echo $data[0]['MidSecTitle'];
            } else { 
              echo 'Live Order Tracking';
            } ?>
          </strong>

          <?if(!empty($data[0]['MidSecContent'])) { 
              echo $data[0]['MidSecContent'];
          } else { 
            echo '<p>know where your order is at all times, from the restaurant to your doorstep</p>';
          } ?>
          
        </li>

        <li>

          <b>
              <?if(!empty($data[0]['MidThirdImage'])) { ?>
                <img src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['MidThirdImage'];?>" alt="" />
              <?php } else { ?>
                 <img alt="" src="assets/img/food-home/lightning-fast-delivery.png">
              <?php } ?>
          </b> 
          <strong>
            <?if(!empty($data[0]['MidThirdTitle'])) { 
              echo $data[0]['MidThirdTitle'];
            } else { 
              echo 'Lightning-Fast Delivery';
            } ?>
          </strong>

          <?if(!empty($data[0]['MidThirdContent'])) { 
              echo $data[0]['MidThirdContent'];
          } else { 
            echo '<p>Experience our superfast delivery,for food delivered fresh &amp; on time</p>';
          } ?>

        </li>

      </ul>
      <div style="clear:both;"></div>
    </div>
  </div>
  <!-- -->

  <div class="download-app-today">
    <div class="download-app-today-inner">
      <div class="download-app-today-text">
        <h3>
          <?if(!empty($data[0]['ThirdRightTitle'])) { echo $data[0]['ThirdRightTitle'];} else { echo 'restaurant  in your pocket';}?>
        </h3>
          <?if(!empty($data[0]['ThirdRightContent'])) {
            echo $data[0]['ThirdRightContent'];
          } else { 
            echo 'Tap the app, select your favorite restaurant and place the delicious food. That is what our app will do. No words are necessary when you place an order with us because we know exactly what you want.';
          }?>
       
        <span class="app-links">
          <a href="<?=$IPHONE_APP_LINK?>">
            <?if(!empty($data[0]['AppStoreImg'])) { ?>
              <img alt="" src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['AppStoreImg'];?>">
            <?php } else { ?>
              <img alt="" src="assets/img/food-home/app-store.jpg">
            <?php } ?>
          </a
          ><a href="<?=$ANDROID_APP_LINK?>">
            <?if(!empty($data[0]['PlayStoreImg'])) { ?>
              <img alt="" src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['PlayStoreImg'];?>">
            <?php } else { ?>
              <img alt="" src="assets/img/food-home/play-store.jpg">
            <?php } ?>
          </a>
        </span>
      </div>
      <div class="mobile-app-screen">
        <span> 
          <?if(!empty($data[0]['ThirdLeftImg1'])) { ?>
            <img alt="" src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['ThirdLeftImg1'];?>">
          <?php } else { ?>
            <img alt="" src="assets/img/food-home/app-screen1.jpg">
          <?php } ?>
        </span>
        <b>
          <?if(!empty($data[0]['ThirdLeftImg2'])) { ?>
            <img alt="" src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['ThirdLeftImg2'];?>">
          <?php } else { ?>
            <img alt="" src="assets/img/food-home/app-screen2.jpg">
          <?php } ?>
        </b>
        <em>
          <?if(!empty($data[0]['ThirdLeftImg3'])) { ?>
            <img alt="" src="<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['ThirdLeftImg3'];?>">
          <?php } else { ?>
            <img alt="" src="assets/img/food-home/app-screen3.jpg">
          <?php } ?>
        </em>
      </div>
      <div style="clear:both;"></div>
    </div>
  </div>

  <div class="about-banner" style="background-image:url('assets/img/page/home/<?php echo $data[0]['AboutUsBgImage']; ?>')">
    <div class="about-banner-inner">
      <h4>
        <?if(!empty($data[0]['AboutUsTitle'])) { echo $data[0]['AboutUsTitle'];} else { echo 'About Us';}?> 
      </h4>
      <strong>
        <?if(!empty($data[0]['AboutUsSecondTitle'])) { echo $data[0]['AboutUsSecondTitle'];} else { echo 'The Entire Eatery Range At Your Fingertips';}?>
      </strong>
       <?if(!empty($data[0]['AboutUsContent'])) { 
          echo $data[0]['AboutUsContent'];
        } else { 
          echo '<p>Whatever you fancy, wherever you fancy, we have the entire range of cuisines available just for you to satisfy your hunger. Select the meal of your choice, from an eatery of your choice, at the date and time of your choice and hey presto, you will a five-star culinary experience in the comfort of your home, office or wherever you are. Your wish is our command!</p><a href="about">Read More</a>';
        }?>
      <div style="clear:both;"></div>
    </div>
  </div>


  <div class="most-trusted-restaurants">
    <div class="most-trusted-restaurants-inner">
      <h4>
        <?if(!empty($data[0]['HomeRestuarantSectionLabel'])) { 
          echo $data[0]['HomeRestuarantSectionLabel'];
        } else { 
          echo 'Most Trusted Restaurants';
        }?>
      </h4>
      <div class="clearfix">
        <ul class="clearfix">
            <?php  $dlang = $_SESSION['sess_lang'];
            for($i=0;$i<count($RestaurantData);$i++) {
            ?>
              <li>
                <label style="background-image:url('<?=$tconfig["tsite_upload_images"].$db_home_drv[$i]['vImage']?>')"></label>
                <strong><?=$RestaurantData[$i]['vCompany'];?></strong>
                <p><?php echo $RestaurantData[$i]['cusines']?> 
                  <b>
                  <img src="assets/img/food-home/star.jpg" alt=""/> 
                  <?php if($RestaurantData[$i]['vAvgRating'] == ''){ 
                    echo '';
                   } else {
                    echo $RestaurantData[$i]['vAvgRating'];
                  } ?></b>
               </p>
              </li>
            <?php } ?>
        </ul>
      </div>
    </div>
  </div>
</div>