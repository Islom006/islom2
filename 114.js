
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

const loginTab = document.getElementById("loginTab");
const registerTab = document.getElementById("registerTab");

const openLogin = document.getElementById("openLogin");
const openRegister = document.getElementById("openRegister");

const goRegister = document.getElementById("goRegister");
const goLogin = document.getElementById("goLogin");

const formTitle = document.getElementById("formTitle");
const formSubtitle = document.getElementById("formSubtitle");
const formMessage = document.getElementById("formMessage");

// Xabarni ko'rsatish
function showMessage(message, success = false) {
    formMessage.textContent = message;
    formMessage.hidden = false;

    formMessage.classList.toggle("success", success);
    formMessage.classList.toggle("error", !success);
}

// Xabarni tozalash
function clearMessage() {
    formMessage.textContent = "";
    formMessage.hidden = true;
    formMessage.classList.remove("success", "error");
}

// Login oynasini ko'rsatish
function showLogin() {
    loginForm.hidden = false;
    registerForm.hidden = true;

    loginTab.classList.add("active");
    registerTab.classList.remove("active");

    formTitle.textContent = "Xush kelibsiz!";
    formSubtitle.textContent =
        "Hisobingizga kirish uchun ma'lumotlarni kiriting.";

    clearMessage();
}

// Ro'yxatdan o'tish oynasini ko'rsatish
function showRegister() {
    loginForm.hidden = true;
    registerForm.hidden = false;

    loginTab.classList.remove("active");
    registerTab.classList.add("active");

    formTitle.textContent = "Yangi hisob yarating";
    formSubtitle.textContent =
        "Ro'yxatdan o'tish uchun ma'lumotlaringizni kiriting.";

    clearMessage();
}

// Oynalar orasida o'tish
loginTab.addEventListener("click", showLogin);
openLogin.addEventListener("click", showLogin);
goLogin.addEventListener("click", showLogin);

registerTab.addEventListener("click", showRegister);
openRegister.addEventListener("click", showRegister);
goRegister.addEventListener("click", showRegister);

// Parolni ko'rsatish va yashirish
document.querySelectorAll(".show-password").forEach(button => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.dataset.target);

        if (!input) return;

        const isHidden = input.type === "password";

        input.type = isHidden ? "text" : "password";
        button.textContent = isHidden ? "Yashirish" : "Ko'rsatish";
    });
});

// RO'YXATDAN O'TISH
registerForm.addEventListener("submit", async event => {
    event.preventDefault();
    clearMessage();

    const firstName =
        document.getElementById("firstName").value.trim();

    const lastName =
        document.getElementById("lastName").value.trim();

    const email =
        document.getElementById("registerEmail").value.trim();

    const phone =
        document.getElementById("registerPhone").value.trim();

    const password =
        document.getElementById("registerPassword").value;

    const passwordConfirm =
        document.getElementById("passwordConfirm").value;

    if (password.length < 8) {
        showMessage("Parol kamida 8 ta belgidan iborat bo'lsin.");
        return;
    }

    if (password !== passwordConfirm) {
        showMessage("Parollar bir-biriga mos emas.");
        return;
    }

    const submitButton = registerForm.querySelector(
        'button[type="submit"]'
    );

    submitButton.disabled = true;
    submitButton.textContent = "Yuborilmoqda...";

    try {
        const response = await fetch("register.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                firstName,
                lastName,
                email,
                phone,
                password,
                passwordConfirm
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            showMessage(
                result.message || "Ro'yxatdan o'tishda xatolik."
            );
            return;
        }

        registerForm.reset();
        showLogin();

        showMessage(
            "Ro'yxatdan muvaffaqiyatli o'tdingiz! Endi tizimga kiring.",
            true
        );

        document.getElementById("loginEmail").value = email;

    } catch (error) {
        showMessage(
            "Serverga ulanib bo'lmadi. XAMPP va PHP fayllarini tekshiring."
        );
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Hisob yaratish <span>→</span>';
    }
});

// LOGIN — login.php bilan ulanish
loginForm.addEventListener("submit", async event => {
    event.preventDefault();
    clearMessage();

    const email =
        document.getElementById("loginEmail").value.trim();

    const password =
        document.getElementById("loginPassword").value;

    if (!email || !password) {
        showMessage("Email va parolni kiriting.");
        return;
    }

    const submitButton = loginForm.querySelector(
        'button[type="submit"]'
    );

    submitButton.disabled = true;
    submitButton.textContent = "Tekshirilmoqda...";

    try {
        const response = await fetch("login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email,
                password
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            showMessage(
                result.message || "Email yoki parol noto'g'ri."
            );
            return;
        }

        showMessage(
            "Tizimga muvaffaqiyatli kirdingiz! Bosh sahifaga o'tilmoqda.",
            true
        );

        loginForm.reset();

        setTimeout(() => {
            window.location.href = "index.html";
        }, 1000);

    } catch (error) {
        showMessage(
            "Serverga ulanib bo'lmadi. XAMPP va login.php faylini tekshiring."
        );
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Kirish <span>→</span>';
    }
});

// Dastlab login oynasi ko'rinadi
showLogin();