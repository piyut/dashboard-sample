var page = 0;
    itemsPerPage = 10;
    avatar = $('#avatar');

getDataFromApi(page);

function getDataFromApi(page) {
    var url = 'https://sampleapp-65ghcsaysse.qiscus.com/api/v2.1/rest/get_user_list';
    $("<div class='box-loading'><span class='icon-loading'></span> LOADING</div>").appendTo( ('.box-table') );

    $.ajax({
        url: url,
        type: 'get',
        data: {
            limit: itemsPerPage,
            page: page
        },
        headers: {
            QISCUS_SDK_SECRET: 'dc0c7e608d9a23c3c8012c6c8572e788',
            'Content-Type':'application/x-www-form-urlencoded'
        },
        dataType: 'json',
        success: function (data) {
            $('table > tbody').empty();
            $('.box-loading').remove();
            if ($(".box-title > h3 > .total-user").children().length > 0) {
                $(".box-title > h3 > .total-user").empty();
            }
            listingData(data, page);
        }
    });
}

function listingData(data, page) {
    if ($("table > tbody").children().length > 0) {
        $("table > tbody").empty();
    }
    if (data.results.users.length > 0) {
        $("<span> ("+ data.results.meta.total_data +")</span>").appendTo( ('.box-title > h3 > .total-user') );
        $.each(data.results.users, function (index, val) {
            var key = ((page - 1) * itemsPerPage) + (index + 1);
            var username = val.username ? val.username : '-';
            var createDate = DateFormat.format.date(val.created_at, 'dd/MM/yyyy HH:mm:ss')
            var updateDate = DateFormat.format.date(val.updated_at, 'dd/MM/yyyy HH:mm:ss')
            $("<tr><th scope='row'>" + key + "</th><td class='text-capitalize'><img style='margin-right: 10px;' class='img-circle' width='48' height='48' src=" + val.avatar_url + ">" + val.email + "</td><td>" + createDate + "</td><td>" + updateDate + "</td></tr>").appendTo( ('tbody') );
        });
        $('#pagination').twbsPagination({
            totalPages: Math.ceil(data.results.meta.total_data / itemsPerPage),
            onPageClick: function (evt, page) {
                page = page;
                getDataFromApi(page);
            }
        });
    } else {
        $("<tr></tr><tr><td colspan='5' class='text-center'><div class='icon-empty-user'></div><div class='info-empty'>User Data Not Found</div><div class='instruction-empty'>You can add user to use it on your app that using Qiscus SDK</div><div><button type='button' class='btn btn-default' data-toggle='modal' data-target='#createUserModal'><span class='icon-user'></span> Add User </button></div></td></tr>").appendTo( ('tbody') );
    }
}

/**
 * create new user
 */
window.URL = window.URL || window.webkitURL;
function handleFiles(files) {
    for (var i = 0; i < files.length; i++) {
        avatar.attr('src', window.URL.createObjectURL(files[i]));
        var info = document.createElement("div");
        info.innerHTML = "Size: " + files[i].size + " bytes";
        avatar.parent().append(info);
    }
    avatar = files[0];
}

$('input,textarea').on('keyup change keypress', function () {
    var send        = $('#buttonCreateUser')
    if ($('input#email').val() != '' && $('input#username').val() != '' && $('input#password').val() != '') {
        send.removeClass('disable')
    } else {
        send.addClass('disable')
    }
})

$('#buttonCreateUser').on("click", function () {
    var self = $('#buttonCreateUser');
    var email = $('#email').val();
    var password = $('#password').val();
    var username = $('#username').val();
    var avatar_url = $('#avatar_url').val();
    self.empty();
    self.css('background', '#F2994A');
    self.append("<span class='icon-loading icon-loading-white'></span> Creating User");
    self.addClass('disabled');
    var url = 'https://sampleapp-65ghcsaysse.qiscus.com/api/v2/rest/login_or_register'
    $.ajax({
        url: url,
        method: 'POST',
        type: 'POST',
        data: {
            email: email,
            password: password,
            username: username,
            avatar_url: avatar_url
        },
        headers: {
            QISCUS_SDK_SECRET: 'dc0c7e608d9a23c3c8012c6c8572e788',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        dataType: 'json',
        success: function (data) {
            $('#createUserModal').modal('hide')
            setTimeout(function () {
                location.reload()
            }, 1000);
        },
        error: function (error) {
            self.empty();
            self.append('Add User')
            self.css('background', '#2ACB6E');
        }
    });
});