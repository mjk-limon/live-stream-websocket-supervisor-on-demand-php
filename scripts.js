document.addEventListener('DOMContentLoaded', function () {
    const player = videojs("vid1");

    const messageSection = document.getElementById('message-section');
    const loginSection = document.getElementById('login-section');

    const usernameInput = document.getElementById('username');
    const loginButton = document.getElementById('login-button');
    const logoutButton = document.getElementById('logout-button');

    const storedUsername = localStorage.getItem('username');

    const checkUser = username => {
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
                    localStorage.setItem('username', JSON.stringify(data.user));
                    loginSection.style.display = 'none';
                    messageSection.style.display = 'block';

                    setTimeout(() => connectWebSocket(data.user), 1000);
                    return true;
                }

                localStorage.removeItem('username');
                alert('User not found');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    const connectWebSocket = user => {
        const { name, pic } = user;
        const websocket = new WebSocket(`wss://${window.location.host}/stream/ws/`);

        const message_input = document.getElementById('message-input');
        const chat_box = document.getElementById('chat-box');
        let keepAlive;

        const sendMessage = (type, message) => {
            websocket.send(JSON.stringify({ type: type, message: message, name: name, pic: pic }));
        }

        websocket.onopen = function (ev) {
            sendMessage('system', `${name} যুক্ত হয়েছেন।`);
            keepAlive = setInterval(() => sendMessage('info', "I'm alive"), 10000);
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
                        <div class="media mb-2 pb-1 ${name == user_name ? 'flex-row-reverse' : ''}">
                            <img src="${user_pic}" class="mx-3 rounded-circle">
                            <div class="media-body">
                                <div><strong>${user_name}</strong></div>
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
            keepAlive && clearInterval(keepAlive);
            chat_box.innerHTML += `<div class="system_error">চ্যাট সার্ভার সচল নেই।</div>`;
        };

        websocket.onclose = function () {
            keepAlive && clearInterval(keepAlive);
            chat_box.innerHTML += `<div class="system_error">সংযোগ বিচ্ছিন্ন</div>`;
        }

        document
            .getElementById('send-button')
            .addEventListener('click', function () {
                if (message_input.value == "") {
                    alert("Enter Some message Please!");
                    return;
                }

                sendMessage('usermsg', message_input.value);
                message_input.value = '';
            })

        message_input
            .addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    if (message_input.value == "") {
                        alert("Enter Some message Please!");
                        return;
                    }

                    sendMessage('usermsg', message_input.value);
                    message_input.value = '';
                }
            });
    };

    player.ready(function () {
        // player.muted(true);
        // player.play();
    });

    if (storedUsername) {
        const { id } = JSON.parse(storedUsername);
        checkUser(id);
    }

    loginButton.addEventListener('click', function () {
        checkUser(usernameInput.value.trim());
    });

    logoutButton.addEventListener('click', function () {
        localStorage.removeItem('username');
        location.reload();
    });
});