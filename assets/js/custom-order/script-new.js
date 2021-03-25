$(document).ready(function(){

    
    
   
  /*   $(document).on('click','.minus',function(){
        
        var value = parseInt(document.getElementById('number').value,10);
        value = isNaN(value) ? 0 : value;
        value++;
        document.getElementById('number').value = value;
    })

    $(document).on('click','.plus',function(){
        var value = parseInt(document.getElementById('number').value,10);
        value = isNaN(value) ? 0 : value;
        value < 1 ? value = 1 : '';
        value--;
        document.getElementById('number').value = value;
    }) */

    /**************this script for set equal height of element******************/
    
        
    $(document).on('click','.cart-data-row span.remove_ele',function(){
        $(this).closest('.cart-data-row').remove();
    })

    $(document).on('click','.close-icon',function(){
        $(this).closest('.product-model-overlay').removeClass('active');
    })


    


    $('body').keydown(function(e) {
        if (e.keyCode == 27) {
            $('.close-icon').trigger('click')
        }
        console.log(e);
    });
    
    var e = $.Event("keydown", {
        keyCode: 27
    });
        
    $('#escape').click(function() {
        $("body").trigger(e);
    });
  /*   $(document).on('click','.menu-item-block,.cart-data-row span.cart-item-name',function(){
        $('.product-model-overlay').addClass('active');
    }) */
    // $(document).click(function(event) {
    //     //if you click on anything except the modal itself or the "open modal" link, close the modal
    //     if (!$(event.target).closest(".product-model").length) {
    //         $('.product-model-overlay').removeClass('active');
    //     }
    // });
    
    $menu = $('.more-menu ul');
    $(document).mouseup(function (e) {
    if (!$menu.is(e.target) // if the target of the click isn't the container...
    && $menu.has(e.target).length === 0) // ... nor a descendant of the container
    {
        $menu.removeClass('opened');
    }
    });
    $(document).on('click','.filter-data button',function(){
        $menu.toggleClass('opened');
    });
    
    var FIXEDTOP = $('.product-list-right').offset().top - 70;
    var FOOTEROFFSET = window.innerHeight - $('footer').outerHeight();
    
    var FIXEDLEFT = $('.leftbar-filter').offset().left;

    var CHECKOFLEFT = $('.checkout-block').offset().left;
    var CHECKOFTOP = $('.checkout-block').offset().top;

    $(window).resize(function(){
        var FIXESLEFT = $('.leftbar-filter').offset().left;
        $('.leftbar-filter').css('left',FIXESLEFT);
    })
    $(window).scroll(function (event) {
        var scroll = $(window).scrollTop();
        // Do something
        if(scroll > FIXEDTOP){
            var FIXESLEFT = $('.filter-data').offset().left;
            $('.leftbar-filter,.filter-data').addClass('fixed_ele');
            $('.filter-data').css('left',FIXESLEFT);
        }else {
            $('.leftbar-filter,.filter-data').removeClass('fixed_ele');
            $('.filter-data').css('left','auto');
        }

        if(scroll > CHECKOFTOP) {
            $('.checkout-block').addClass('fixed_ele')
            $('.checkout-block').css('left',CHECKOFLEFT);
        }else {
            $('.checkout-block').removeClass('fixed_ele')
        }
        
    });
    // $(window).scroll(function (event) {
    //     var scroll = $(window).scrollTop();
    //     // Do something
    //     let leftbar_filter_bottom = $('.leftbar-filter').offset().top + $('.leftbar-filter').outerHeight();
    //     let footer_top = $('footer').offset().top;
        
    //     if(scroll > FIXEDTOP ){
    //         if(leftbar_filter_bottom - footer_top > 0){
    //             $('.leftbar-filter,.filter-data').removeClass('fixed_ele');
    //             $('.filter-data').css('left','auto');
    //         }else{    
    //             var FIXESLEFT = $('.filter-data').offset().left;
    //             $('.leftbar-filter,.filter-data').addClass('fixed_ele');
    //             $('.filter-data').css('left',FIXESLEFT);   
    //         }
    //     }else {
    //         $('.leftbar-filter,.filter-data').removeClass('fixed_ele');
    //         $('.filter-data').css('left','auto');
    //     }

    //     if(scroll > CHECKOFTOP) {
    //         $('.checkout-block').addClass('fixed_ele')
    //         $('.checkout-block').css('left',CHECKOFLEFT);
    //     }else {
    //         $('.checkout-block').removeClass('fixed_ele')
    //     }
        
    // });
    

   
    


    // Add smooth scrolling to all links
    $(".filter-data ul li a").on('click', function(event) {
        // Make sure this.hash has a value before overriding default behavior
        if (this.hash !== "") {
        // Prevent default anchor click behavior
        event.preventDefault();

        // Store hash
        var hash = this.hash;

        // Using jQuery's animate() method to add smooth page scroll
        // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
        $('html, body').animate({
            scrollTop: window.innerWidth <= 991 ? $(hash).offset().top-100 : $(hash).offset().top-120
        }, 500, function(){
    
            // Add hash (#) to URL when done scrolling (default click behavior)
           // window.location.hash = hash;
        });
        } // End if
    });
    if($('.scroll-data').length > 0 && window.innerWidth > 1025){
        $('.scroll-data').overlayScrollbars({
            className: "os-theme-dark"
        }); 
    }
    if($('.cart-data').length > 0){
        $('.cart-data').overlayScrollbars({
            className: "os-theme-dark"
        }); 
    }
  
   
   

})