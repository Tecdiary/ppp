var socket = null;
try {
    socket = new WebSocket('ws://127.0.0.1:6441');
    socket.onopen = function () {
        var curr = $('.status');
        for (var i = 0; i < curr.length; i++) {
            curr[i].classList.add('is-success');
            curr[i].innerHTML = "UP & RUNNING";
        }
        return;
    };
    socket.onmessage = function (msg) {
        $('#message').show();
        $('#message').find('.notification').addClass('is-info').html(msg.data);
        setTimeout(function () {
            $('#message').hide();
            $('#message').find('.notification').text('');
        }, 5000)
        return;
    };
    socket.onclose = function () {
        var curr = $('.status');
        for (var i = 0; i < curr.length; i++) {
            curr[i].classList.add('is-danger');
            curr[i].innerHTML = "NOT CONNECTED";
        }
        return;
    };
} catch (e) {
    console.log(e);
}
checkStatus = function() {
    if (socket.readyState == 1) {
        socket.send(JSON.stringify({
            type: 'check-status'
        }));
        return false;
    } else {
        $('.status').addClass('is-loading');
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 500);
        return false;
    }
}

$(document).ready(function() {
    $('.nav-toggle').click(function() {
        $(this).toggleClass('is-active');
        $(this).next('.nav-menu').toggleClass('is-active');
    });

    $('#type').change(function () {
        var type = $(this).val();
        if (type == 'network') {
            $('.network').show();
            $('.path').hide();
        } else {
            $('.network').hide();
            $('.path').show();
        }
    });
    var type = $('#type').val();
    if (type == 'network') {
        $('.network').show();
        $('.path').hide();
    } else {
        $('.network').hide();
        $('.path').show();
    }

    $(document).on('click', '.test-print', function (e) {
        e.preventDefault();
        var print_id = $(this).attr('data-printer-id');
        printTest(print_id);
    });

});


function testData() {

    receipt = {};
    receipt.store_name = "Test Biller\n";

    receipt.header = "";
    receipt.header += "Test Biller\n";
    receipt.header += "Biller adddress\n";
    receipt.header += "City Country\n";
    receipt.header += "Tel: 012345678";
    receipt.header += "\n\n";
    receipt.header += "GST Reg: 123456789\n";
    receipt.header += "VAT Reg: 987654321\n";
    receipt.header += "\n";

    receipt.info = "";
    receipt.info += "Date: 08/05/2017 10:38" + "\n";
    receipt.info += "Sale No/Ref: 15" + "\n";
    receipt.info += "Sales Associate: Owner Owner" + "\n\n";
    receipt.info += "Customer: Walk-in Customer" + "\n";

    receipt.items = "";
    receipt.items += "#1 FFR07 - Yellow Watermelon           *Z" + "\n";
    receipt.items += "   2.500 kg x 2.50                   6.25" + "\n";
    receipt.items += "#2 FFR06 - Watermelon                  *Z" + "\n";
    receipt.items += "   2.500 kg x 2.50                   6.25" + "\n";

    receipt.totals = "";
    receipt.totals += "Total:                              12.50" + "\n";
    receipt.totals += "Grand Total:                        12.50" + "\n";
    receipt.totals += "Paid Amount:                        12.50" + "\n";
    receipt.totals += "Due Amount:                          0.00" + "\n";

    receipt.payments = '';
    receipt.payments += "Paid by:                             Cash" + "\n";
    receipt.payments += "Amount:                             12.50" + "\n";
    receipt.payments += "Change:                              0.00" + "\n";

    receipt.footer = "";
    receipt.footer += " Thank you for shopping with us. \nPlease come again\n\n";

    return receipt;

}

function printTest(printer_id) {
    if (socket.readyState == 1) {
        $.each(printers, function() {
            if (this.id == printer_id) {
                printer = this;
            }
        });
        var receipt_data = testData();
        var socket_data = {
            'printer': printer,
            'text': receipt_data
        };
        socket.send(JSON.stringify({
            type: 'print-receipt',
            data: socket_data
        }));
        return false;
    } else {
        alert('Unable to connect to socket, please make sure that server is up and running fine.');
        return false;
    }
}
