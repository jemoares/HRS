
function get_reservations(search='') {

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/cancelled_reservations.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        document.getElementById('table-data').innerHTML = this.responseText;
    }

    xhr.send('get_reservations&search='+search);
}





function cancel_reservation(id)
{
    if(confirm("Are you sure you want to cancel this reservation?"))
    {
        let data = new FormData();
        data.append('booking_id', id);
        data.append('cancel_reservation', '');
    
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/cancelled_reservations.php", true);

        xhr.onload = function() {             

            if (this.responseText == 1) {
                alert('success', 'Reservation Cancelled');
                get_reservations();
            }  
            else {
                alert('error', 'Server Down' );
            }
        }
        xhr.send(data);
    }
}

function paid_reservation(id)
{
    
    if(confirm("Are you sure this reservation has been paid?"))
    {
        let data = new FormData();
        data.append('booking_id', id);
        data.append('paid_reservation', '');
    
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/cancelled_reservations.php", true);

        xhr.onload = function() {             

            if (this.responseText == 1) {
                alert('success', 'Reservation Confirmed');
                get_reservations();
            }  
            else {
                alert('error', 'Server Down' );
            }
        }
        xhr.send(data);
    }
}


    
window.onload = function() {
    get_reservations();
}