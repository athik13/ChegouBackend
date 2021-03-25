$(document).ready(function () {
    // window.addEventListener('load', function () {
    //     $.fn.textWidth = function(){
    //         var html_org = $(this).html();
    //         var html_calc = '<span>' + html_org + '</span>';
    //         $(this).html(html_calc);
    //         var width = $(this).find('span:first').width();
    //         $(this).html(html_org);
    //         return width;
    //     };
    //     $( "ul.tab-row li" ).each(function( index ) {
    //         let TEXTWIDTH = $(this).find('a').textWidth();
    //         if(TEXTWIDTH > 179) {
    //             $(this).find('a').wrap('<marquee width="100%" direction="left"></marquee>')
    //         }
    //     });
    // })
    // $.fn.textWidth = function(){
    //     var html_org = $(this).html();
    //     var html_calc = '<span>' + html_org + '</span>';
    //     $(this).html(html_calc);
    //     var width = $(this).find('span:first').width();
    //     $(this).html(html_org);
    //     return width;
    // };
    // $( "ul.tab-row li" ).each(function( index ) {
    //     let TEXTWIDTH = $(this).find('a').textWidth();
    //     if(TEXTWIDTH > 179) {
    //         $(this).find('a').wrap('<marquee width="100%" direction="left"></marquee>')
    //     }
    // });
    window.addEventListener('load', function () {
        if($('#chat-widget').length > 0) {
            $('.footer-bottom .footer-inner').css('padding','35px 15px 90px 15px');
            $('.gotop').css('bottom','130px');
        }
    })
    let NUMOFCHILD = $( "ul.tab-row li").length;
    let WIDTH = 90/NUMOFCHILD;
    let CALCWIDTH = WIDTH+'%'
    $( "ul.tab-row li").css('width',CALCWIDTH)
    $(".service-block").click(function(){
        //this will find the selected website from the dropdown
        var go_to_url = $(this).find('a').attr('href');
        
        //this will redirect us in new tab
        window.open(go_to_url, '_blank');
      });
    /***********tab-switch start*****************/
    $(document).on('click', 'ul.tab-row li', function () {
        var DATAID = $(this).attr('data-id');
        var DATASRC = $(this).attr('data-src');
        $('ul.tab-row li').removeClass('active');
        $(this).addClass('active');
        $('.categories-caption').removeClass('active');
        $(document).find('#' + DATAID).addClass('active');
        $('.banner-image').fadeOut(500);
        $(document).find('#' + DATASRC).fadeIn(500);  
    })
    $('ul.tab-row li[data-src="1"]').trigger('click')
    /***********tab-switch end*****************/

    //for login
    /***********tab-switch start*****************/
    $(document).on('click', 'ul.tab-switch li', function () {
        var DATAID = $(this).attr('data-id');
        var DATADESC = $(this).attr('data-desc');
        $('ul.tab-switch li').removeClass('active');
        $(this).addClass('active');
        $('.login-caption,.note-holder,.gen-forms').removeClass('active');
        $(document).find('#' + DATAID).addClass('active');
        $(document).find('.' + DATAID).addClass('active');
        $('.login-right p').text(DATADESC);
        // $('.parallax-window').parallax();
        floating_remove();
        // setTimeout(function(){
        //     if($('.parallax-window').length > 0) {
        //         $('.parallax-window').parallax();
        //     }
        // }, 500);
    })
    /***********tab-switch end*****************/

    /************************ floating label *********************** */
    $(document).on('focusin','.form-group input,.form-group textarea',function(){
        $(this).closest('.form-group').addClass('floating');
    });
    $(document).on('focusout','.form-group input,.form-group textarea',function(){
        if($(this).val() == ""){
            $(this).closest('.form-group').removeClass('floating');
        }
    });
	
    

    /***********mobile menu toggle*****************/
    $(document).on('click', '.menu-ico', function (e) {
        $(this).toggleClass('active')
        $('ul.navmenu-links').toggleClass('active');
    })

    /***********parallax function init*****************/
    /*setTimeout(() => {
        if($('.parallax-window').length > 0) {
            $('.parallax-window').parallax();
        }
    }, 300);
    
    setTimeout(function(){
        if($('.parallax-window').length > 0) {
            $('.parallax-window').parallax();
        }
    },300);
    */

    function responsive_handle() {
        if (window.innerWidth < 992) {
            $('.login-left').insertAfter('.login-right');
        } else {
            $('.login-left').insertbefore('.login-right');
        }
    }
    if ($('.login-left').leggth > 0) {
        responsive_handle();
    }

    $(window).resize(function () {
        if ($('.login-left').leggth > 0) {
            responsive_handle();
        }
        // if($('.parallax-window').length > 0) {
        //     $('.parallax-window').parallax();
        // }
    })

    /**************** stikey header ****************/
    $(document).ready(function(){
        if ($('.banner-section.taxi-app .tab-row-holding').length > 0) {
            window.onscroll = function () {
                myFunction()
            };

            var header = document.querySelector(".banner-section.taxi-app .tab-row-holding");
            var sticky = window.innerWidth > 767 ? header.offsetTop+70:header.offsetTop+53;

            function myFunction() {
                if (window.pageYOffset > sticky) {
                    header.classList.add("sticky");
                } else {
                    header.classList.remove("sticky");
                }
            }
        }
    })
    /******************* go to section script*******************/
    $('.banner-section.taxi-app ul.tab-row li').bind('click', function (e) {
        e.preventDefault(); // prevent hard jump, the default behavior
        var target = $(this).find('a').attr("href"); // Set the target as variable
        // perform animated scrolling by getting top-position of target-element and set it as scroll target
        $('html, body').stop().animate({
            scrollTop: $(target).offset().top - 70
        }, 800, function () {
            //location.hash = target; //attach the hash (#jumptarget) to the pageurl
        });
        return false;
    });
    window.addEventListener('load', function () {
        var scrollDistance = $(window).scrollTop()+75;
        $(window).scroll(function () {
            var scrollDistance = $(window).scrollTop()+75;
           // var top_decs = $('.how-it-works-section').offset().top-10;/
                // Assign active class to nav links while scolling
                $('.page-section').each(function (i) {
                    if ($(this).position().top <= scrollDistance) {
                        $('.banner-section.taxi-app ul.tab-row li.active').removeClass('active');
                        $('.banner-section.taxi-app ul.tab-row li').eq(i).addClass('active');
                    }
                });
    
                if (scrollDistance > 500) {
                    $(".gotop").addClass('active')
                } else {
                    $(".gotop").removeClass('active')
                }
                scrollDistance > 100?$('header').addClass('shadow'):$('header').removeClass('shadow');
        }).scroll();
        scrollDistance > 100?$('header').addClass('shadow'):$('header').removeClass('shadow');
    });

    /**************** go to top ***************/
    $(".gotop").click(function () {
        var offset = 0; //Offset of 20px

        $('html, body').animate({
            scrollTop: $('body').offset().top + offset
        }, 500);
    });

    /**************** go to multi delivery ***************/
    $(".go-md").click(function () {
        $('html, body').animate({
            scrollTop: $('.multi_delivery').offset().top - 90
        }, 300);
    });
    
    /**************** go to single delivery ***************/
    $(".go-sd").click(function () {
        $('html, body').animate({
            scrollTop: $('.single_delivery').offset().top - 90
        }, 300);
    });

    /**************this script for set equal height of element******************/
    
    $(document).on('click','.msg_close',function(e){
        //$(this).closest('.form-err').remove();
        $(".error-login-v").hide();
    })
    
    /*$(document).on('click','.price-caption i',function(){
        $('.vehicle-details-popup').addClass('active');
    })*/
	$(document).on('click','.price-caption i',function(){
        $('#vehicle-popup').addClass('active');
    })
    
    $(document).on('click','.vehicle-details-popup .close-icon',function(){
        $('.vehicle-details-popup').removeClass('active');
    })
    
    $.fn.equalHeight = function() {
        var maxHeight = 0;
        return this.each(function(index, box) {
            var boxHeight = $(box).height();
            maxHeight = Math.max(maxHeight, boxHeight);
        }).height(maxHeight);
    };
    function EQUAL_HEIGHT(){
        $('.destination-caption strong').equalHeight();
        $(window).resize(function(){
             $('.destination-caption strong').css('height','auto');
             $('.destination-caption strong').equalHeight();
        });
    }

    $.fn.equalHeight1 = function() {
        var maxHeight = 0;
        return this.each(function(index, box) {
            var boxHeight = $(box).height();
            maxHeight = Math.max(maxHeight, boxHeight);
        }).height(maxHeight);
    };
    function EQUAL_HEIGHT1(){
        $('.destination-caption span').equalHeight1();
        $(window).resize(function(){
             $('.destination-caption span').css('height','auto');
             $('.destination-caption span').equalHeight1();
        });
    }
    
    $.fn.equalHeight2 = function() {
        var maxHeight = 0;
        return this.each(function(index, box) {
            var boxHeight = $(box).height();
            maxHeight = Math.max(maxHeight, boxHeight);
        }).height(maxHeight);
    };
    function EQUAL_HEIGHT2(){
        $('.charters-caption strong').equalHeight2();
        $(window).resize(function(){
             $('.charters-caption strong').css('height','auto');
             $('.charters-caption strong').equalHeight2();
        });
    }
    
    $.fn.equalHeight3 = function() {
        var maxHeight = 0;
        return this.each(function(index, box) {
            var boxHeight = $(box).height();
            maxHeight = Math.max(maxHeight, boxHeight);
        }).height(maxHeight);
    };
    function EQUAL_HEIGHT3(){
        $('.charters-caption span').equalHeight3();
        $(window).resize(function(){
             $('.charters-caption span').css('height','auto');
             $('.charters-caption span').equalHeight3();
        });
    }

    $.fn.equalHeight4 = function() {
        var maxHeight = 0;
        return this.each(function(index, box) {
            var boxHeight = $(box).height();
            maxHeight = Math.max(maxHeight, boxHeight);
        }).height(maxHeight);
    };
    function EQUAL_HEIGHT4(){
        $('.benefits ul li').equalHeight4();
        $(window).resize(function(){
             $('.benefits ul li').css('height','auto');
             $('.benefits ul li').equalHeight4();
        });
    }

	$(document).on('click','.profile_edit_btn',function(){
        $('.profile_edit').addClass('active');
    })
    $(document).on('click','.cancel_btn',function(){
        $('.profile_edit').removeClass('active');
    })

    $(document).on('click','.menu-icoholder-side i',function(){
        $('body').toggleClass('side_menu');
    })
    function floating_remove() {
        if($( "form[name='login_form'] .form-group").length>0) {
            $( "form[name='login_form'] .form-group" ).each(function( index ) {
                $this = $(this).find('input');
                console.log($this.val())
                if($this.val() != ""){
                    $this.closest('.form-group').addClass('floating');
                }else {
                    $this.closest('.form-group').removeClass('floating');
                }
            })
        }
        
        if($( "form[name='frmsignup'] .form-group").length>0) { 
            $( "form[name='frmsignup'] .form-group" ).each(function( index ) {
                $this = $(this).find('input');
                if($this.val() != ""){
                    $this.closest('.form-group').addClass('floating');
                }else {
                    $this.closest('.form-group').removeClass('floating');
                }
            })
        }
    }

    function general_label() {
        $(document).on('focusin','.form-group input,.form-group textarea',function(){
            $(this).closest('.form-group').addClass('floating');
        });
        $(document).on('focusout','.form-group input,.form-group textarea',function(){
            if($(this).val() == ""){
                $(this).closest('.form-group').removeClass('floating');
            }
        });

        $(document).on('focusin','.form-group input,.form-group textarea',function(){
            $(this).parent('relation-parent').closest('.form-group').addClass('floating');
        });
        $(document).on('focusout','.form-group input,.form-group textarea',function(){
            if($(this).val() == ""){
                $(this).parent('relation-parent').closest('.form-group').removeClass('floating');
            }
        });

        $( ".general-form .form-group" ).each(function( index ) {
            $this = $(this).find('input');
            if($this.val() == ""){
                $this.closest('.form-group').removeClass('floating');
            }else {
                $this.closest('.form-group').addClass('floating');   
            }
        })
        $( ".gen-from .form-group" ).each(function( index ) {
            $this = $(this).find('input');
            if($this.val() == ""){
                $this.closest('.form-group').removeClass('floating');
            }else {
                $this.closest('.form-group').addClass('floating');   
            }
        })
        $( ".general-form .form-group" ).each(function( index ) {
            $this = $(this).find('textarea');
            if($this.val() == ""){
                $this.closest('.form-group').removeClass('floating');
            }else {
                $this.closest('.form-group').addClass('floating');   
            }
        })
    }

   
   /* setTimeout(() => {
        floating_remove(); 
    }, 200);*/
   
    setTimeout(function(){
        floating_remove(); 
    }, 200);

    general_label();
    floating_remove(); 

    window.addEventListener('load', function () {
        setTimeout(function(){
            floating_remove(); 
        }, 200);
        EQUAL_HEIGHT();
        EQUAL_HEIGHT1();
        EQUAL_HEIGHT2();
        EQUAL_HEIGHT3();
        EQUAL_HEIGHT4();
    })

	if($('.stepper').length > 0){
    var stepper = document.querySelector('.stepper');
    var stepperInstace = new MStepper(stepper, {
      // options
      firstActive: 0, // this is the default
      validationFunction: false,
   })
	}
    
    var mytimevar;

    $('#btn_fly_stations').click(function(){
        var userType = $('[name="userType1"]').val();

        if(userType == "rider")
        {
            elements = document.querySelector('.step');
            stepperInstace.openStep(2, elements);
            clearTimeout(mytimevar);
            if($('#eType_design').val() != "delivery")
            {
                mytimevar = setTimeout(function(){ 
                    $('.stepper li:nth-child(2)').removeClass('active');
                }, 100);    
            }
            else{
                mytimevar = setTimeout(function(){ 
                    $('.stepper li:nth-child(1)').removeClass('active');
                }, 100);
            }
            
        }
    });


    $('#vehicle_type_back').click(function(){
        var userType = $('[name="userType1"]').val();

        if(userType == "rider")
        {
            elements = document.querySelector('.step');
            stepperInstace.openStep(0, elements);
            clearTimeout(mytimevar);
            if($('#eType_design').val() != "delivery")
            {
                mytimevar = setTimeout(function(){ 
                    $('.stepper li:nth-child(2)').removeClass('active');
                }, 100);
            }
            else{
                mytimevar = setTimeout(function(){ 
                    $('.stepper li:nth-child(1)').removeClass('active');
                }, 100);   
            }

        }
    });


    $('#stationdropdown_li, #vehicle_type').click(function(){
        var userType = $('[name="userType1"]').val();
        var attr = $(this).attr('data-step');
        if(userType == "rider")
        {
            if(!($(this).hasClass('active')) && (typeof attr !== typeof undefined && attr !== false))
            {
                $(".stepper li").removeClass("active");
                $(".stepper li .step-content").hide();
                elements = document.querySelector('.step');
                stepperInstace.openStep($(this).data('step'), elements);
            }
        }
        else{
            $('#user_details_label').show();
        }
    });

    $('.next-step').keypress(function (e) {
        if (e.which == 13) {
          $('.next-step').trigger('click')
          //return false;    //<---- Add this line
        }
    });
    $('.previous-step').keypress(function (e) {
        if (e.which == 13) {
          $('.previous-step').trigger('click')
          //return false;    //<---- Add this line
        }
    });
      
    $(document).on('click','[data-dismiss="modal"]',function(e){
        e.preventDefault();
        $(this).closest('.custom-modal-main').removeClass('active');
    });
    $('body').keydown(function(e){
        if (e.which==27){
            $('[data-dismiss="modal"]').trigger('click')

        }
    });

    $(document).on('click','[data-toggle="modal"]',function(e){
        e.preventDefault();
        var data_target = $(this).attr('data-target');
        $('.custom-modal-main').removeClass('active');
        $(document).find(data_target).addClass('active');
    });

    $(document).on('click','.extra-details h3',function(e){
        $(this).toggleClass('active')
        $('.inv-destination-data ul.no-icons').slideToggle();
    })


    // custom-modal-main responsive

    $(document).ready(function(){
        $("#custId").click(function(){
          $("body").addClass("intro-new-one");
        });
        $(".custom-modal-main").click(function(){
            $("body").removeClass("intro-new-one");
          });
      });

      $( document ).ready( function(){
        setMaxWidth();
        setMaxHeight();
          
        function setMaxWidth() {
        $( ".custom-modal-main" ).css( "maxWidth", ( $( window ).width() * 90 | 0 ) + "px" );
        }
         function setMaxHeight() {
        $( ".custom-modal-main" ).css( "maxHeight", ( $( window ).height() * 90 | 0 ) + "px" );
        }
    
    });
    
// custom-modal-main responsive


    
    //$(document).on('click','.header-right ul li.lang',function(e){
    //    if(window.innerWidth < 1024){
    //        $('.dropdown-content').slideToggle();
    //    }
    //})


    /***********change mobile menu location in mobile*****************/
    function menuHandle() {
        if (window.innerWidth < 768) {
            $('.header-left .logo ~ ul.navmenu-links').insertAfter('.header-inner');
        } else {
            $('header .header-inner ~ ul.navmenu-links').insertAfter('.logo');
        }
    }
    menuHandle();
    $(window).resize(function () {
        menuHandle();
    })

    function table_responsive() {
        $('table.dataTable.custom-table').wrap('<div class="table-responsive"></div>');
    }

    window.addEventListener('load', function () {
        table_responsive();
    })
    
    /**************** go to order delivery ***************/
    $(".go-order").click(function (e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('.ordernow').offset().top - 90
        }, 300);
    });
    // if($('.upload-block').length > 0){
    //     $( ".profile-earning ul li" ).each(function( index ) {
    //         $(this).find('.upload-block .button-block').insertAfter($(this).find('.upload-block-inner')) 
    //         $(this).find('.upload-block .icon-close').insertBefore($(this).find('.doc-image-block img'));
    //     });
    // }
    
    $(document).ready(function(){
		var old_link = '';
		$(".navmenu-links .has-level-menu a").click(function(e){
            //e.preventDefault();
			$(".lang .dropdown-content").hide();
		  	$(this).next(" .dropdown-content").show();
			  $("body").addClass("hidden-overflow");
			  if($(".navmenu-links li").find("a.active").attr('href')!='#') {
				old_link = $(".navmenu-links li").find("a.active").attr('href');
			  }
			  $(".navmenu-links li a").removeClass("active");
			  $(this).addClass("active"); 
		});
		$(".dropdown-content .row h3 span").click(function(){
			$(this).closest(".dropdown-content").hide()
			$("body").removeClass("hidden-overflow");
			$('.navmenu-links li a').each(function(){
				if($(this).attr('href')==old_link) {
					$(this).addClass("active");
				}
			});
			$(".navmenu-links .has-level-menu a").removeClass("active");
		})
		
		$(".header-right .lang > a").click(function(e){
			e.preventDefault();
		  $(".navmenu-links .has-level-menu  .dropdown-content").hide();
		  $(this).next(".dropdown-content").show();
		  $("body").addClass("hidden-overflow");
		  if($("header-right li").find("a.active").attr('href')!='#') {
				old_link = $(".header-right li").find("a.active").attr('href');
			}
			  $(".header-right li a").removeClass("active");
			  $(this).addClass("active");
		});
		$(".dropdown-content .row h3 span").click(function(){
			$(this).closest(".dropdown-content").hide()
			$("body").removeClass("hidden-overflow");
			$('.header-right li a').each(function(){
				if($(this).attr('href')==old_link) {
					$(this).addClass("active");
				}
			});
			$(".navmenu-links .has-level-menu a").removeClass("active");
		})
			
	  });
})

