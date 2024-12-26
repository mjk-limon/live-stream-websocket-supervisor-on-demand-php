document.addEventListener('DOMContentLoaded', function () {
    const player = videojs("vid1");

    const messageSection = document.getElementById('message-section');
    const loginSection = document.getElementById('login-section');

    const usernameInput = document.getElementById('username');
    const loginButton = document.getElementById('login-button');

    const storedUsername = localStorage.getItem('username');

    const connectWebSocket = user => {
        const { name, pic } = user;
        const websocket = new WebSocket(`wss://${window.location.host}/stream/ws/`);

        const message_input = document.getElementById('message-input');
        const chat_box = document.getElementById('chat-box');

        const sendMessage = () => {
            if (message_input.value == "") {
                alert("Enter Some message Please!");
                return;
            }

            websocket.send(JSON.stringify({
                type: 'usermsg',
                message: message_input.value,
                name: name,
                pic: pic,
            }));

            message_input.value = '';
        }

        websocket.onopen = function (ev) {
            const msg = {
                type: 'system',
                message: `${name} যুক্ত হয়েছেন।`,
                name: name,
                pic: pic,
            };

            websocket.send(JSON.stringify(msg));
        };

        websocket.onmessage = function (ev) {
            const response = JSON.parse(ev.data);
            const res_type = response.type;
            const user_message = response.message;
            const user_name = response.name;
            const user_pic = response.pic;

            switch (res_type) {
                case 'usermsg':
                    chat_box.innerHTML += `
                        <div class="media mb-2 pb-1">
                            <img src="${user_pic}" class="mr-3 rounded-circle">
                            <div class="media-body">
                                <h5 class="m-0">${user_name}</h5>
                                <div>${user_message}</div>
                            </div>
                        </div>
                    `;
                    break;

                case 'system':
                    chat_box.innerHTML += `<div class="media mb-3 text-info">${user_message}</div>`;
                    break;
            }

            chat_box.scrollTop = chat_box.scrollHeight;
        };

        websocket.onerror = function (ev) {
            chat_box.innerHTML += `<div class="system_error">চ্যাট সার্ভার সচল নেই।</div>`;
        };

        websocket.onclose = function () {
            chat_box.innerHTML += `<div class="system_error">সংযোগ বিচ্ছিন্ন</div>`;
        }

        document
            .getElementById('send-button')
            .addEventListener('click', function () {
                sendMessage();
            })

        message_input
            .addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    sendMessage();
                }
            });
    };

    player.ready(function () {
        // player.muted(true);
        // player.play();
    });

    if (storedUsername) {
        loginSection.style.display = 'none';
        messageSection.style.display = 'flex';

        connectWebSocket(JSON.parse(storedUsername));
    }

    loginButton.addEventListener('click', function () {
        var username = usernameInput.value.trim();
        if (username) {
            fetch('./app/check_user.php', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `username=${username}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        connectWebSocket(data.user);

                        localStorage.setItem('username', JSON.stringify(data.user));
                        loginSection.style.display = 'none';
                        messageSection.style.display = 'flex';

                        return true;
                    }

                    alert('Username already exists or is currently connected.');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });
});