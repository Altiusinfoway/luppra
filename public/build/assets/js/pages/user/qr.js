"use strict";

const device_id = $('#device_id').val();
const base_url  = $('#base_url').val();
const device_status = $('#device_status').val();
var   attampt   = 0;
var   session_attampt=0;

function whatsappStatusMessage(status) {
	const messages = {
		connected: 'WhatsApp session is connected.',
		connecting: 'WhatsApp is still connecting. Please try again in a moment.',
		disconnected: 'WhatsApp session is disconnected. Please reconnect this device.',
		qr_required: 'Please scan the QR code to connect this device.',
		not_ready: 'WhatsApp session is not ready yet. Please reconnect this device.',
	};

	return messages[String(status || '').trim()] || messages.not_ready;
}

console.log('device id',device_id);
checkSession();

//new_cd
$('.logged-alert').hide();
$('.server_disconnect').hide();



//create session request for this device
function createSession() {

    console.log('------qr js=> createSession() =>line= 14 ----------');
	attampt++;

	if (attampt == 6)
    {
        //original attampt == 6

         console.log('------qr js=> createSession() => if ==6 =>line =18 ----------');
		clearInterval(sessionMake);
		const image=`<img src="${base_url}/public/uploads/waiting.jpeg" class="w-50">`;
		$('.qr-area').html(image);

		Swal.fire({
			title: 'Opps!',
			text: whatsappStatusMessage('qr_required'),
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Close',
			confirmButtonText: 'Refresh This Page'
		}).then((result) => {
			if (result.value == true) {
				location.reload();
			}
		});
		return false;
	}
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

    console.log('------qr js=> createSession() => before create-session route  ----------');
        //sending ajax request
        $.ajax({
        	type: 'POST',
        	url: base_url+'/create-session/'+device_id,
        	dataType: 'json',
        	success: function(response) {
        		const image=`<img src="${response.qr}" class="w-90">`;
        		$('.qr-area').html(image);
                 console.log('create-session success & hide');
        		$('.server_disconnect').hide();
        		$('.progress').show();




        	},
        	error: function(xhr, status, error) {

        		const image=`<img src="${base_url}/public/uploads/disconnect.webp" class="w-50"><br>`;
        		$('.qr-area').html(image);
                 console.log('create-session error & show');
        		$('.server_disconnect').show();

        		if (xhr.status == 500) {
        			clearInterval(checkSessionRecurr);
        			clearInterval(sessionMake);
        		}
        	}
        });
    }

//check is device logged in
function checkSession() {
     console.log('------qr js=> checkSession() => line= 73 ----------');
    console.log('url full', base_url+'/check-session/'+device_id);
	session_attampt++;
	if (session_attampt >= 10) {
		clearInterval(checkSessionRecurr);
		return false;
	}


	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	$.ajax({
		type: 'POST',
		url: base_url+'/check-session/'+device_id,
		dataType: 'json',
		success: function(response) {
            console.log('---route success check-session =>line=91');
			if (response.connected === true) {
				clearInterval(checkSessionRecurr);
				clearInterval(sessionMake);

				NotifyAlert('success', null, response.message || whatsappStatusMessage(response.status));
				$('.loggout_area').show();
                //new_cd
                console.log('check-session => success=>if');
                // $('.server_disconnect').hide();





				const image=`<img src="${base_url}/public/uploads/connected.png" class="w-50"><br>`;
				$('.qr-area').html(image);

				$('.logged-alert').show();
				$('.progress').hide();
				$('.helper-box').show();

				device_status == '0' ? congratulations() : '';
			}
			else{
                  console.log('check-session => success=>else');
				session_attampt == 1 ? createSession() : '';
			}
		},
		error: function(xhr, status, error) {
			if (xhr.status == 500) {
				clearInterval(checkSessionRecurr);
				clearInterval(sessionMake);
				const image=`<img src="${base_url}/public/uploads/disconnect.webp" class="w-50"><br>`;
				$('.qr-area').html(image);
                console.log('check-session error');
				$('.server_disconnect').show();
                //new_cd
                $('.logged-alert').hide();
			}

		}
	});
}


	//if click logout button
	$('.logout-btn').on('click',function(){

		Swal.fire({
			title: 'Are you sure want to logout?',
			text: "Once make logout you have to make login useing qr",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			cancelButtonText: 'No Please',
			confirmButtonText: 'Yes make logout'
		}).then((result) => {
			if (result.value == true) {
				let previous_btn=$('.logout-btn').html();

				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});
				$.ajax({
					type: 'POST',
					url: base_url+'/logout-session/'+device_id,
					dataType: 'json',
					beforeSend: function () {
						$('.logout-btn').html('<i class="fas fa-spinner"><i>&nbspPlease Wait...');
						$('.logout-btn').attr('disabled','');
					},
					success: function(response) {
						NotifyAlert('success',null, response.message);
						$('.logout-btn').html(previous_btn);
						$('.logout-btn').hide();
						$('.logout-btn').removeAttr('disabled');

						location.reload();
					},
					error: function(xhr, status, error) {
						NotifyAlert('error', xhr);
						$('.logout-btn').html(previous_btn);
						$('.logout-btn').removeAttr('disabled');

					}
				});
			}

		});


	});

	const sessionMake= setInterval(function(){
		createSession();
	}, 12000);

	const checkSessionRecurr=setInterval(function(){
		checkSession();
	},5000);
