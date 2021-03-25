// function show_alert(title = "", content = "", positive_btn = "", negative_btn = "", natural_btn = "", callback, isCloseAlertOnClick = true) { // Do not REMOVE Orignal function
function show_alert(title, content, positive_btn, negative_btn, natural_btn, callback, isCloseAlertOnClick = true) {			
			title = title || '';
			content = content || '';
			positive_btn = positive_btn || '';
			negative_btn = negative_btn || '';
			natural_btn = natural_btn || '';
			isCloseAlertOnClick = isCloseAlertOnClick;
          var str = "<div class='custom-modal-first-div active' id='custom-alert'>" 
           + "<div class='custom-modal-sec-div' role='document'>"
                              if(title!="") {
                                       str += "<div class='custom-model-header'><h4 class='custom-modal-title' id='inactiveModalLabel'>"+ title +"</h4>"
                                        + "<i class='icon-close' data-dismiss='modal' style='font-size:20px;'></i></div>";
                              }
                              str +=  "<div class='custom-model-body'>"+ content +"</div>"
                              + "<input type='hidden' name='iDriverId_temp' id='iDriverId_temp'>"
                              +"<div class='custom-model-footer'>";
                              if(natural_btn!="") {
                              str = str + "<button type='button' class='btn custom-modal-genbtn' onclick='handle_click(2, "+callback + ", "+isCloseAlertOnClick+")'>"+ natural_btn +"</button>";      
                              }
                              str +="<div class='button-block'>";
                              if(positive_btn!="") {
                              str = str + "<button type='button' class='gen-btn' onclick='handle_click(0, "+callback + ", "+isCloseAlertOnClick+")'>"+ positive_btn +"</button>";
                              }
                              if(negative_btn!="") {
                               str = str + "<button type='button' class='gen-btn' onclick='handle_click(1, "+callback + ", "+isCloseAlertOnClick+")'>"+ negative_btn +"</button>";
                              }
                             
                              str = str + "</div></div>"
                    + "</div>"
                    + "</div>";
          if($("#custom-alert").length > 0 ){
                    $('body').find('#custom-alert').remove();
                    $('body').append(str);
          } else {
                    $('body').append(str);
          }
}

function handle_click(btn_id, callback, isCloseAlertOnClick) {
      if(isCloseAlertOnClick == true){
          $('.custom-modal-first-div').removeClass('active');
      }
      if(typeof callback!=='undefined') {
        callback(btn_id);
      }
}

  $(document).on('click','[data-dismiss="modal"]',function(e){
      e.preventDefault();
      $(this).closest('.custom-modal-first-div').removeClass('active');
  });

  $(document).on('click','[data-toggle="modal"]',function(e){
      e.preventDefault();
      var data_target = $(this).attr('data-target');
      $('.custom-modal-first-div').removeClass('active');
      $(document).find(data_target).addClass('active');
  });

