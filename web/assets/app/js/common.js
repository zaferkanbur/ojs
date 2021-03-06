// to serialize form data as Json object
$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
$.fn.setTypeForHook = function () {
    this.each(function () {
        this.type = 'source';
    });
    return this;
};

$(document).ready(function () {
    if ($(".maskissn").length) {
        $(".maskissn").inputmask({mask: "####-###M", definitions: {'#': {validator: "[0-9]", cardinality: 1}, 'M': {validator: "[0-9X]", cardinality: 1}}});
    }
    $(".contrastColor").each(function () {
        $(".contrastColor").css("color", (parseInt($(this).css("backgroundColor"), 16) > 0xffffff / 2) ? 'black' : 'white');
    });
    $(".validate-form").validationEngine({
        promptPosition: 'inline', validateNonVisibleFields: true,
        updatePromptsPosition: true
    });
    $(".wysihtml5").wysihtml5({
        toolbar: {
            "font-styles": false,
            "emphasis": true,
            "lists": false,
            "html": false,
            "link": true,
            "image": false,
            "color": false,
            "blockquote": true
        }
    });
    if ($(".select2-element").length) {
        $(".select2-element").select2();
        $(".select2-element").select2('container').setTypeForHook();
        $.valHooks['source'] = {
            get: function (el) {
                var el_id = el.id.replace('s2id_','');
                el=$("#"+el_id);
                console.dir(el);
                return el.select2("val");
            },
            set: function (el, val) {
                var el_id = el.id.replace('s2id_','');
                el=$("#"+el_id);
                console.dir(el);
                console.log("setted");
                $(el).select2("val", val);
            }
        };
    }
    if ($(".autocomplete").length){
        $(".autocomplete").each(function(){
            var list_url=$(this).data("list");
            var get_url =$(this).data("get");
            $(this).autocompleter({url_list:list_url, url_get: get_url});
        })
    }
    function formatResult(item) {
        return item.name;
    }

    function formatSelection(item) {
        return '<b>' + item.name + '</b>';
    }

    $('input.tags').tagsinput({
        tagClass: 'label label-info',
        trimValue: true,
        confirmKeys: [13, 44, 188, 59]
    });
    var tagAutocompleteInput = $('input[data-role=tagsinputautocomplete]');
    var typeList = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: tagAutocompleteInput.attr('data-list')+'?q=%QUERY',
            wildcard: '%QUERY'
        }
    });
    typeList.initialize();
    tagAutocompleteInput.tagsinput({
        typeaheadjs: {
            name: 'typeList',
            displayKey: 'text',
            valueKey: 'text',
            source: typeList.ttAdapter()
        }
    });
    $(".select2-tags").select2({
        multiple: true,
        //Allow manually entered text in drop down.
        createSearchChoice: function (term, data) {
            if ($(data).filter(function () {
                    return this.text.localeCompare(term) === 0;
                }).length === 0) {
                return {id: term, text: term};
            }
        },
        ajax: {
            url: 'api/public/search/tags',
            dataType: 'json',
            type: "GET",
            delay: 300,
            data: function (params) {
                return {
                    q: '(.*)' + params + '(.*)',
                    verified: true
                };
            },
            results: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name,
                            slug: item.name,
                            id: item.id
                        };
                    })
                };
            },
            cache: true
        },
//            escapeMarkup: function (markup) {
//                return markup;
//            }, // let our custom formatter work
        minimumInputLength: 1
//            templateResult: formatResult,
//            templateSelection: formatSelection

    });
    $('.select2-tags').each(function () {
        var value = $(this).val();
        if(value)
        {
            var $that = $(this);
            $.ajax({
                url:'api/public/search/tagsByIds',
                data: 'ids='+value,
                dataType: 'json',
                success: function(data){
                    $that.select2('data', data).change();
                }
            })
        }
    });
    $(".select2-tags").select2('container').setTypeForHook();

    $(".select2-container").removeClass('validate[required]');
    $(document).on('pjax:send', function () {
        $('#loading').show();
    });
    $(document).on('pjax:complete', function () {
        $('#loading').hide();
    });

    $('a[title]').tooltip();
    $(".panel-heading.toggle-body").click(function () {
        $(this).next(".panel-body").slideToggle();
    });

    /* float placeholders to labels */
    $('.float-label').jvFloat();

    //Loads the correct sidebar on window load,
    //collapses the sidebar on window resize.

    $(window).bind("load resize", function () {
        //console.log($(this).width());
        if ($(this).width() < 768) {
            $('div.sidebar-collapse').addClass('collapse');
        } else {
            $('div.sidebar-collapse').removeClass('collapse');
        }
    });
    if ($('#issuetree').length > 0 && typeof alternateData != "undefined") {
        $('#issuetree').treeview({
            showTags: true,
            data: alternateData,
            onNodeSelected: function (event, node) {
                /* @todo we should get issue content by ajax */
                // $.pjax({url: node.href, container: '#issue-container'})

                console.log(node.text + ':' + node.href);
            }
        });
    }

    var $btnSets = $('#responsive'),
        $btnLinks = $btnSets.find('a');

    $btnLinks.click(function (e) {
        e.preventDefault();
        $(this).siblings('a.active').removeClass("active");
        $(this).addClass("active");
        var index = $(this).index();
        $("div.user-menu>div.user-menu-content").removeClass("active");
        $("div.user-menu>div.user-menu-content").eq(index).addClass("active");
    });

    $("[rel='tooltip']").tooltip();

    $('.view').hover(
        function () {
            $(this).find('.caption').slideDown(250); //.fadeIn(250)
        },
        function () {
            $(this).find('.caption').slideUp(250); //.fadeOut(205)
        }
    );
    window.alert = function (message) {
        var n = noty({
            text: message,
            type: "alert",
            dismissQueue: true,
            layout: 'center',
            theme: "relax",
            closeWith: ['button', 'click'],
            maxVisible: 20,
            modal: true
        });
    };
    window.notify = function (message, type) {
        var n = noty({
            text: message,
            type: type,
            dismissQueue: true,
            layout: 'topCenter',
            theme: "relax",
            closeWith: ['button', 'click'],
            maxVisible: 20,
            modal: false
        });
    };
});


/*
 * SIDEBAR MENU
 * ------------
 * This is a custom plugin for the sidebar menu. It provides a tree view.
 * 
 * Usage:
 * $(".sidebar).tree();
 * 
 * Note: This plugin does not accept any options. Instead, it only requires a class
 *       added to the element that contains a sub-menu.
 *       
 * When used with the sidebar, for example, it would look something like this:
 * <ul class='sidebar-menu'>
 *      <li class="treeview active">
 *          <a href="#>Menu</a>
 *          <ul class='treeview-menu'>
 *              <li class='active'><a href=#>Level 1</a></li>
 *          </ul>
 *      </li>
 * </ul>
 * 
 * Add .active class to <li> elements if you want the menu to be open automatically
 * on page load. See above for an example.
 */
(function ($) {
    "use strict";

    $.fn.tree = function () {

        return this.each(function () {
            var btn = $(this).children("a").first();
            var menu = $(this).children(".treeview-menu").first();
            var isActive = $(this).hasClass('active');

            //initialize already active menus
            if (isActive) {
                menu.show();
                btn.children(".fa-angle-left").first().removeClass("fa-angle-left").addClass("fa-angle-down");
            }
            //Slide open or close the menu on link click
            btn.click(function (e) {
                e.preventDefault();
                if (isActive) {
                    //Slide up to close menu
                    menu.slideUp();
                    isActive = false;
                    btn.children(".fa-angle-down").first().removeClass("fa-angle-down").addClass("fa-angle-left");
                    btn.parent("li").removeClass("active");
                } else {
                    //Slide down to open menu
                    menu.slideDown();
                    isActive = true;
                    btn.children(".fa-angle-left").first().removeClass("fa-angle-left").addClass("fa-angle-down");
                    btn.parent("li").addClass("active");
                }
            });

            /* Add margins to submenu elements to give it a tree look */
            menu.find("li > a").each(function () {
                var pad = parseInt($(this).css("margin-left")) + 10;

                $(this).css({"margin-left": pad + "px"});
            });

        });

    };
    function deleteSubmit(link) {
        var form = $(document.createElement('form')),
            object = $(link);
        object.after(
            form.attr({
                method: 'post',
                action: object.attr('href')
            })
                .append('<input type="hidden" name="_method" value="delete" />')
                .append('<input type="hidden" name="_token" value="' + object.data('token') + '" />')
        );
        form.submit();
    }
    $('a[data-method=delete]').click(function() {
        if($(this).data('confirm')) {
            if(confirm($(this).data('confirm'))) {
                deleteSubmit(this);
            }
            return false;
        }
        deleteSubmit(this);
        return false;

    });
    $('.select-search-type').click(function(){
        var searchType = $(this).attr('data-type');
        var searchTypeText = $(this).text();
        $('#search-type-text').html(searchTypeText);
        $('#search-type').val(searchType);
    });

}(jQuery));