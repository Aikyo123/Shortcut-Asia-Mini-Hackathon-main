
async function api(url, data = null, method = 'POST') {
    const opts = { method, headers: {} };
    if (data) {
        if (method === 'GET') {
            url += '?' + new URLSearchParams(data);
        } else {
            const fd = new FormData();
            for (const [k, v] of Object.entries(data)) fd.append(k, v);
            opts.body = fd;
        }
    }
    const res  = await fetch(url, opts);
    return res.json();
}


function fmt(num) {
    return 'RM ' + parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}


function showAlert(id, msg, type = 'error') {
    const el = document.getElementById(id);
    if (!el) return;
    el.className = `alert alert-${type} show`;
    el.textContent = msg;
    setTimeout(() => el.classList.remove('show'), 5000);
}


function setLoading(btn, loading) {
    if (loading) {
        btn._origText = btn.innerHTML;
        btn.innerHTML = '<span class="loading"></span>';
        btn.disabled = true;
    } else {
        btn.innerHTML = btn._origText;
        btn.disabled = false;
    }
}


async function checkSession(redirect = true) {
    try {
        const data = await api('php/auth.php', { action: 'check' }, 'GET');
        if (!data.logged_in && redirect) {
            window.location.href = 'login.html';
        }
        return data;
    } catch {
        if (redirect) window.location.href = 'login.html';
        return null;
    }
}


function initSidebar(user) {
    if (!user) return;
    document.querySelectorAll('.user-name').forEach(el => el.textContent = user.name);
    document.querySelectorAll('.user-email').forEach(el => el.textContent = user.email);
    document.querySelectorAll('.avatar').forEach(el => {
        el.style.background = user.avatar_color;
        el.textContent = user.name.charAt(0).toUpperCase();
    });
}


async function logout() {
    await api('php/auth.php', { action: 'logout' });
    window.location.href = 'login.html';
}


function initHamburger() {
    const ham = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    if (!ham || !sidebar) return;
    ham.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', e => {
        if (!sidebar.contains(e.target) && !ham.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}


function today() { return new Date().toISOString().slice(0, 10); }
function formatDate(d) {
    return new Date(d).toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' });
}
function getGreeting() {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
}


const TIPS = [
    { icon: '🎓', text: '<strong>Freelance tip:</strong> Platforms like Upwork, Fiverr, and Freelancer.com are great for students. Start with RM20–50 gigs!' },
    { icon: '📱', text: '<strong>Sell unused stuff:</strong> Carousell and Facebook Marketplace let you earn from items collecting dust at home.' },
    { icon: '🍔', text: '<strong>Food delivery:</strong> Grab Food and Lalamove delivery side hustles can earn you RM30–80 per day on weekends.' },
    { icon: '📸', text: '<strong>Content creation:</strong> TikTok and Instagram pay creators. Even small accounts can get brand deals!' },
    { icon: '💡', text: '<strong>Tutoring:</strong> Offer tutoring to Form 4/5 students. RM20–40/hour is standard in Malaysia.' },
    { icon: '📊', text: '<strong>50/30/20 rule:</strong> 50% needs, 30% wants, 20% savings. Apply this to any income you earn!' },
    { icon: '🛒', text: '<strong>Cashback apps:</strong> Use ShopBack and BigPay when shopping to get money back on every purchase.' },
    { icon: '⚡', text: '<strong>Quick task apps:</strong> TaskRabbit-style apps let you earn by helping people with errands around campus.' },
];

function randomTip() {
    return TIPS[Math.floor(Math.random() * TIPS.length)];
}

window.addEventListener('DOMContentLoaded', initHamburger);
