$(document).ready(function() {
    /**
     * Function reload page
     **/
    function reloadPage(url) {
        if (typeof(url) === 'undefined') {
            url = window.location.href;
        }
        $('body').load(url, function(response, status, xhr) {
            if ( status == "error" ) {
                var msg = "Sorry but there was an error: ";
                console.log( msg + xhr.status + " " + xhr.statusText );
            }
        });
    }

    $('#order').click(function(e) {
        e.preventDefault();
        var d = $(this).attr('data-order');
        if (d == 'DESC') {
            d = 'ASC';
            $(this).text('Date ↑');          
        }
        else {
            d = 'DESC';
            $(this).text('Date ↓');
        }
        $(this).attr('data-order', d);
        $('.table tbody').load(window.location.href + "?order="+d+" .table tbody tr", function(response, status, xhr) {
            if ( status == "error" ) {
                var msg = "Sorry but there was an error: ";
                console.log( msg + xhr.status + " " + xhr.statusText );
            }
        });
    });

    /**
     * Pagination reload
     **/
    $('.pagination a').click(function(e) {
        e.preventDefault();
        reloadPage($(this).attr('href'));
    });

     /**
      * Dropdown puce insert input
      **/
    $('.modal-body .dropdown-menu a').click(function(e) {
        e.preventDefault();
        var name = $(this).attr('data-name');
        $('.modal-body .category').attr('value', name);
    });
    $('.table .dropdown-menu a').click(function(e) {
        e.preventDefault();
        var name = $(this).attr('data-name');
        var id = $(this).attr('data-id');
        $('.'+id + ' .category').attr('value', name);
    });

    /**
     * DatePicker
     **/
    $('input[name="created"]').datepicker({ dateFormat: "dd/mm/yy" });

    /**
     * Add Task
     **/
   $('#addTask').click(function(e) {
       e.preventDefault();
       var fname = $('#formAddTask .name').val();
       var fcreated = $('#formAddTask .date').val();
       var fcategory = $('#formAddTask .category').val();

       $('.modal-body .form-group').removeClass('has-error');

       if (fname === '') {
           $('#formAddTask .name').parent().addClass('has-error');
       }
       if (fcreated === '') {
           $('#formAddTask .date').parent().addClass('has-error');
       }
       if (fcategory === '') {
           $('#formAddTask .category').parent().addClass('has-error');
       }
       if (fname != '' && fcategory != '' && fcreated != '') {
           var dataform = {
               name: fname,
               created: fcreated,
               category: fcategory
           };
           $.post("task/add", dataform, function (data) {
               //console.log(data);
               $('#modalAddTask').modal('hide');
               reloadPage();
           });
       }
   });

    /**
     * Update Task
     **/
    $('.updateTask').click(function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var val = {};
        var bol = true;
        $('.formUpdateTask-'+id+' input').each(function(i){
            var value = $(this).val();
            if (value === '') {
                $(this).parent().addClass('has-error');
            }
            val[i] = value;
            if (value == '' && bol == true) {
                bol = false;
            }
        });
        if (bol == true) {
            $.post("task/update", val, function(data) {
                console.log(data);
                reloadPage();
            });
        }
    });

    /**
     * Remove One Task
     **/
    $('.remove').click(function(e) {
        e.preventDefault();
        var id = $(this).attr("data-id");
        $.get("task/delete", { 'id':id }, function(data) {
            //console.log(data);
            reloadPage();
        });
    });

    /**
     * Remove Multiple Tasks
     **/
    $('.removes').click(function(e) {
        e.preventDefault();
        var val = {};
        $('.table tbody :checkbox:checked').each(function(i){
            val[i] = $(this).val();
        });
        $.post("task/delete", val, function(data) {
            //console.log(data);
            reloadPage();
        });
    });

    /**
     * Show Form TR
     **/
    $('tr').dblclick(function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        $('.dataTask-'+id).hide();
        $('.formUpdateTask-'+id).show();
    });

    /**
     * Validate Task
     **/
    $('.validate').click(function(e) {
        e.preventDefault();
        var id = $(this).attr("data-id");
        $.get("task/validate", { 'id': id }, function(data) {
            //console.log(data);
            reloadPage();
        });
    });

    /**
     * Checkbox checked all
     **/
    $('.js-select-all').click(function() {
        if($(this).is(':checked')) {
            $('.js-select').prop('checked', true);
            $('table').find('.js-select:checked').parent().parent().addClass('warning');
            $('.actions').show();
        } else {
            $('.js-select').prop('checked', false);
            $('table').find('.js-select:not(:checked)').parent().parent().removeClass('warning');
            $('.actions').hide();
        }
    });
    $('.js-select').click(function() {
        if($(this).is(':checked')) {
            $(this).parent().parent().addClass('warning');
        } else {
            $(this).parent().parent().removeClass('warning');
        }

        var table = $('table');
        var max = table.find('.js-select').length;
        var count = table.find('.js-select:checked').length;
        if(count == 0) {
            $('.js-select-all').prop('indeterminate', false).prop('checked', false);
            $('.actions').hide();
        } else if(count == max) {
            $('.js-select-all').prop('indeterminate', false).prop('checked', true);
            $('.actions').show();
        } else {
            $('.js-select-all').prop('indeterminate', true);
            $('.actions').show();
        }
    });
});