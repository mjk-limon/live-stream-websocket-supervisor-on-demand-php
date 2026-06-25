const API = '../app/admin';

async function api(path, options = {}) {
  const response = await fetch(`${API}/${path}`, {
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(data.error || 'Request failed');
  }

  return data;
}

function show(el) {
  el.hidden = false;
}

function hide(el) {
  el.hidden = true;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

const loginView = document.getElementById('login-view');
const dashboardView = document.getElementById('dashboard-view');
const loginForm = document.getElementById('login-form');
const loginError = document.getElementById('login-error');

async function checkSession() {
  try {
    const { logged_in } = await api('auth.php');
    if (logged_in) {
      showDashboard();
    }
  } catch {
    /* stay on login */
  }
}

function showDashboard() {
  hide(loginView);
  show(dashboardView);
  loadSettings();
  loadStreams();
  loadUsers();
}

loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  loginError.hidden = true;

  try {
    await api('auth.php', {
      method: 'POST',
      body: JSON.stringify({ password: document.getElementById('password').value }),
    });
    showDashboard();
  } catch (err) {
    loginError.textContent = err.message;
    loginError.hidden = false;
  }
});

document.getElementById('logout-btn').addEventListener('click', async () => {
  await api('auth.php', { method: 'DELETE' });
  location.reload();
});

document.querySelectorAll('#admin-tabs .nav-link').forEach((tab) => {
  tab.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelectorAll('#admin-tabs .nav-link').forEach((t) => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach((p) => hide(p));
    tab.classList.add('active');
    show(document.getElementById(`tab-${tab.dataset.tab}`));
  });
});

async function loadSettings() {
  const data = await api('settings.php');
  document.getElementById('scrolling-text').value = data.scrolling_text;
  document.getElementById('default-avatar').value = data.default_avatar;
}

document.getElementById('settings-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const payload = {
    scrolling_text: document.getElementById('scrolling-text').value,
    default_avatar: document.getElementById('default-avatar').value,
  };

  const newPassword = document.getElementById('new-password').value;
  if (newPassword) {
    payload.admin_password = newPassword;
  }

  try {
    await api('settings.php', { method: 'PUT', body: JSON.stringify(payload) });
    document.getElementById('new-password').value = '';
    alert('Settings saved');
  } catch (err) {
    alert(err.message);
  }
});

async function loadStreams() {
  const { sources } = await api('stream_sources.php');
  const tbody = document.getElementById('streams-table');
  tbody.innerHTML = sources
    .map(
      (s) => `
    <tr>
      <td>${escapeHtml(s.name)}</td>
      <td class="url-cell" title="${escapeHtml(s.dash_url)}">${escapeHtml(s.dash_url)}</td>
      <td class="url-cell" title="${escapeHtml(s.hls_url)}">${escapeHtml(s.hls_url)}</td>
      <td>${
        s.is_active == 1
          ? '<span class="badge badge-active">Active</span>'
          : '<button class="btn btn-sm btn-outline-success activate-stream" data-id="' +
            s.id +
            '">Activate</button>'
      }</td>
      <td>${
        s.is_active == 1
          ? ''
          : '<button class="btn btn-sm btn-outline-danger delete-stream" data-id="' +
            s.id +
            '">Delete</button>'
      }</td>
    </tr>`
    )
    .join('');

  tbody.querySelectorAll('.activate-stream').forEach((btn) => {
    btn.addEventListener('click', async () => {
      await api('stream_sources.php', {
        method: 'PUT',
        body: JSON.stringify({ id: parseInt(btn.dataset.id, 10), activate: true }),
      });
      loadStreams();
    });
  });

  tbody.querySelectorAll('.delete-stream').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete this stream source?')) return;
      await api('stream_sources.php', {
        method: 'DELETE',
        body: JSON.stringify({ id: parseInt(btn.dataset.id, 10) }),
      });
      loadStreams();
    });
  });
}

document.getElementById('stream-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  try {
    await api('stream_sources.php', {
      method: 'POST',
      body: JSON.stringify({
        name: document.getElementById('stream-name').value,
        dash_url: document.getElementById('stream-dash').value,
        hls_url: document.getElementById('stream-hls').value,
      }),
    });
    e.target.reset();
    loadStreams();
  } catch (err) {
    alert(err.message);
  }
});

async function loadUsers() {
  const { users } = await api('users.php');
  const tbody = document.getElementById('users-table');
  tbody.innerHTML = users
    .map(
      (u) => `
    <tr>
      <td><img src="${escapeHtml(u.pic)}" class="user-avatar" alt=""></td>
      <td>${escapeHtml(u.name)}</td>
      <td>${escapeHtml(u.email)}</td>
      <td><button class="btn btn-sm btn-outline-danger delete-user" data-id="${u.id}">Delete</button></td>
    </tr>`
    )
    .join('');

  tbody.querySelectorAll('.delete-user').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm('Remove this user from chat access?')) return;
      await api('users.php', {
        method: 'DELETE',
        body: JSON.stringify({ id: parseInt(btn.dataset.id, 10) }),
      });
      loadUsers();
    });
  });
}

document.getElementById('user-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const payload = {
    name: document.getElementById('user-name').value,
    email: document.getElementById('user-email').value,
  };

  const pic = document.getElementById('user-pic').value.trim();
  if (pic) {
    payload.pic = pic;
  }

  try {
    await api('users.php', { method: 'POST', body: JSON.stringify(payload) });
    e.target.reset();
    loadUsers();
  } catch (err) {
    alert(err.message);
  }
});

checkSession();
