$(document).ready(function() {

    $(document).on("click", "button.close-message-button", function() {

        $("div.header-message").addClass("gone");

        return false;
    });

    $(document).on("click", "button.close-privacy-message", function() {

        $("div.header-message").addClass("gone");

        $.cookie("privacy", "close", { expires : 7, path: '/' });

        return false;
    });

    if ($('#content').length != 0) {

        var sidenav = new Sidenav({

            content: document.getElementById("content"),
            sidenav: document.getElementById("sidenav"),
            backdrop: document.getElementById("backdrop")
        });
    }

    $(document).on("click", ".menu-toggle", function() {

        if (sidenav.isOpened) {

            sidenav.close();

        } else {

            var content = $('.sidebar-menu').html();
            $('#sidenav').find('div.sidenav-content').html(content);

            sidenav.open()
        }
    });

    $("#spotlight-dlg").on("show.bs.modal", function(e) {

        var $this = $(this);

        $(this).find(".modal-footer").addClass("hidden");
        $(this).find(".spotlight-content").addClass("hidden");
        $(this).find(".loader-content").removeClass("hidden");
        $(this).find(".btn-primary").attr("disabled", "disabled");

        $(this).find(".spotlight-content").load("/ajax/spotlight/list", {limit: 25}, function() {

            $this.find(".loader-content").addClass("hidden");
            $this.find(".spotlight-content").removeClass("hidden");
            $this.find(".modal-footer").removeClass("hidden");
            $this.find(".btn-primary").removeAttr("disabled");
        });
    });

    $(document).on('click', '.dropdown__content', function (e) {

        e.stopPropagation();
    });

    $('#close-button').click(function() {

        $(this).parents('.dropdown').find('#settings-button').dropdown('toggle')
    });

    $(document).on('click', '.emoji-item', function() {

        if (options.pageId === "chat") {

            $editor = $("input[name=message_text]");

        } else if (options.pageId === "post" || options.pageId === "image") {

            $editor = $("input[name=comment_text]");

        } else {

            $editor = $("textarea[name=comment]");
        }

        $editor.val($editor.val() + $(this).text());

        $editor.change();

        if (options.pageId === "chat") {

            $(".btn-emoji-picker").dropdown('toggle');
        }

        return false;
    });

    $(document).on('click', '.emoji-items', function() {

        return false;
    });

    $(document).on('click', '.sticker-items', function() {

        return false;
    });

    $("textarea[name=comment]").autosize();
});