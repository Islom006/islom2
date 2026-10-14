```javascript
"use strict";

/* =====================================================
   1. UMUMIY YORDAMCHI FUNKSIYALAR
===================================================== */

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);


/* =====================================================
   2. SIDEBAR / KATALOG MENYU
===================================================== */

const catalogButton = $("#catalogButton");
const sidebar = $("#sidebar");
const sidebarClose = $("#sidebarClose");
const overlay = $("#overlay");

function openSidebar() {
    if (!sidebar) return;

    sidebar.classList.add("active");

    if (overlay) {
        overlay.classList.add("active");
    }

    document.body.classList.add("no-scroll");
}

function closeSidebar() {
    if (!sidebar) return;

    sidebar.classList.remove("active");

    if (overlay) {
        overlay.classList.remove("active");
    }

    document.body.classList.remove("no-scroll");
}

if (catalogButton) {
    catalogButton.addEventListener("click", openSidebar);
}

if (sidebarClose) {
    sidebarClose.addEventListener("click", closeSidebar);
}

if (overlay) {
    overlay.addEventListener("click", closeSidebar);
}


/* =====================================================
   3. ESCAPE TUGMASI
===================================================== */

document.addEventListener("keydown", function (event) {

    if (event.key === "Escape") {
        closeSidebar();
        closeModals();
    }

});


/* =====================================================
   4. KUN / TUN REJIMI
===================================================== */

const themeButton = $("#themeButton");

let savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
}

function updateThemeIcon() {

    if (!themeButton) return;

    if (document.body.classList.contains("dark-mode")) {

        themeButton.innerHTML =
            '<i class="fa-solid fa-sun"></i>';

    } else {

        themeButton.innerHTML =
            '<i class="fa-solid fa-moon"></i>';

    }
}

updateThemeIcon();

if (themeButton) {

    themeButton.addEventListener("click", function () {

        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {

            localStorage.setItem("theme", "dark");

        } else {

            localStorage.setItem("theme", "light");

        }

        updateThemeIcon();

    });

}


/* =====================================================
   5. MODAL OYNALARNI OCHISH
===================================================== */

function openModal(id) {

    const modal = document.getElementById(id);

    if (!modal) return;

    modal.classList.add("active");

    document.body.classList.add("no-scroll");
}

function closeModal(modal) {

    if (!modal) return;

    modal.classList.remove("active");

    if (!document.querySelector(".modal.active")) {
        document.body.classList.remove("no-scroll");
    }
}

function closeModals() {

    $$(".modal").forEach(function (modal) {
        modal.classList.remove("active");
    });

    document.body.classList.remove("no-scroll");
}


/* =====================================================
   6. MODAL YOPISH
===================================================== */

$$(".modal-close").forEach(function (button) {

    button.addEventListener("click", function () {

        const modal =
            button.closest(".modal");

        closeModal(modal);

    });

});


/* =====================================================
   7. MODAL TASHQARISINI BOSISH
===================================================== */

$$(".modal").forEach(function (modal) {

    modal.addEventListener("click", function (event) {

        if (event.target === modal) {
            closeModal(modal);
        }

    });

});


/* =====================================================
   8. LOGIN OYNASI
===================================================== */

const loginButton = $("#loginButton");

if (loginButton) {

    loginButton.addEventListener("click", function () {

        openModal("loginModal");

    });

}


/* =====================================================
   9. REGISTER OYNASI
===================================================== */

const registerButton = $("#registerButton");

if (registerButton) {

    registerButton.addEventListener("click", function () {

        openModal("registerModal");

    });

}


/* =====================================================
   10. LOGIN -> REGISTER
===================================================== */

const openRegister = $("#openRegister");

if (openRegister) {

    openRegister.addEventListener("click", function () {

        closeModals();

        openModal("registerModal");

    });

}


/* =====================================================
   11. REGISTER -> LOGIN
===================================================== */

const openLogin = $("#openLogin");

if (openLogin) {

    openLogin.addEventListener("click", function () {

        closeModals();

        openModal("loginModal");

    });

}


/* =====================================================
   12. PAROLNI KO‘RSATISH / YASHIRISH
===================================================== */

$$(".password-toggle").forEach(function (button) {

    button.addEventListener("click", function () {

        const input =
            button.parentElement.querySelector("input");

        if (!input) return;

        if (input.type === "password") {

            input.type = "text";

            button.innerHTML =
                '<i class="fa-solid fa-eye-slash"></i>';

        } else {

            input.type = "password";

            button.innerHTML =
                '<i class="fa-solid fa-eye"></i>';

        }

    });

});


/* =====================================================
   13. LOGIN FORM
===================================================== */

const loginForm = $("#loginForm");

if (loginForm) {

    loginForm.addEventListener("submit", function (event) {

        event.preventDefault();

        const login =
            loginForm.querySelector(
                'input[name="login"]'
            );

        const password =
            loginForm.querySelector(
                'input[name="password"]'
            );

        if (!login || !password) return;

        if (
            login.value.trim() === "" ||
            password.value.trim() === ""
        ) {

            showNotification(
                "Xatolik",
                "Login va parolni kiriting."
            );

            return;
        }

        showNotification(
            "Muvaffaqiyatli",
            "Siz tizimga kirish uchun ma'lumot yubordingiz."
        );

        loginForm.reset();

        setTimeout(function () {
            closeModals();
        }, 1000);

    });

}


/* =====================================================
   14. REGISTER FORM
===================================================== */

const registerForm = $("#registerForm");

if (registerForm) {

    registerForm.addEventListener("submit", function (event) {

        event.preventDefault();

        const password =
            registerForm.querySelector(
                'input[name="password"]'
            );

        const confirmPassword =
            registerForm.querySelector(
                'input[name="confirmPassword"]'
            );

        if (!password || !confirmPassword) return;

        if (password.value.length < 6) {

            showNotification(
                "Xatolik",
                "Parol kamida 6 ta belgidan iborat bo‘lsin."
            );

            return;
        }

        if (
            password.value !==
            confirmPassword.value
        ) {

            showNotification(
                "Xatolik",
                "Parollar bir xil emas."
            );

            return;
        }

        showNotification(
            "Muvaffaqiyatli",
            "Ro‘yxatdan o‘tish muvaffaqiyatli yakunlandi."
        );

        registerForm.reset();

        setTimeout(function () {

            closeModals();

            openModal("loginModal");

        }, 1000);

    });

}


/* =====================================================
   15. QIDIRUV
===================================================== */

const searchForm = $("#searchForm");
const searchInput = $("#searchInput");

if (searchForm) {

    searchForm.addEventListener("submit", function (event) {

        event.preventDefault();

        const text =
            searchInput.value.trim().toLowerCase();

        if (text === "") {

            showNotification(
                "Qidiruv",
                "Mahsulot nomini yozing."
            );

            return;
        }

        const products =
            $$(".product-card");

        let found = 0;

        products.forEach(function (product) {

            const productText =
                product.textContent.toLowerCase();

            if (productText.includes(text)) {

                product.style.display = "";

                found++;

            } else {

                product.style.display = "none";

            }

        });

        if (found > 0) {

            showNotification(
                "Qidiruv natijasi",
                found + " ta mahsulot topildi."
            );

        } else {

            showNotification(
                "Topilmadi",
                "Bunday mahsulot mavjud emas."
            );

        }

    });

}


/* =====================================================
   16. QIDIRUVNI TOZALASH
===================================================== */

if (searchInput) {

    searchInput.addEventListener("input", function () {

        if (searchInput.value.trim() === "") {

            $$(".product-card").forEach(function (product) {

                product.style.display = "";

            });

        }

    });

}


/* =====================================================
   17. SEVIMLILAR
===================================================== */

let favorites =
    JSON.parse(
        localStorage.getItem("favorites") || "[]"
    );

$$(".favorite-button").forEach(function (button, index) {

    const product =
        button.closest(".product-card");

    if (!product) return;

    const productId =
        product.dataset.id ||
        "product-" + index;

    if (favorites.includes(productId)) {

        button.classList.add("active");

        button.innerHTML =
            '<i class="fa-solid fa-heart"></i>';

    }

    button.addEventListener("click", function () {

        if (favorites.includes(productId)) {

            favorites =
                favorites.filter(function (id) {
                    return id !== productId;
                });

            button.classList.remove("active");

            button.innerHTML =
                '<i class="fa-regular fa-heart"></i>';

            showNotification(
                "Sevimlilar",
                "Mahsulot olib tashlandi."
            );

        } else {

            favorites.push(productId);

            button.classList.add("active");

            button.innerHTML =
                '<i class="fa-solid fa-heart"></i>';

            showNotification(
                "Sevimlilar",
                "Mahsulot sevimlilarga qo‘shildi."
            );

        }

        localStorage.setItem(
            "favorites",
            JSON.stringify(favorites)
        );

        updateFavoriteCounter();

    });

});


function updateFavoriteCounter() {

    const counter =
        $("#favoriteCounter");

    if (counter) {
        counter.textContent =
            favorites.length;
    }

}

updateFavoriteCounter();


/* =====================================================
   18. SAVATCHA
===================================================== */

let cart =
    JSON.parse(
        localStorage.getItem("cart") || "[]"
    );


function saveCart() {

    localStorage.setItem(
        "cart",
        JSON.stringify(cart)
    );

    updateCart();

}


function updateCart() {

    const cartCounter =
        $("#cartCounter");

    const totalQuantity =
        cart.reduce(function (sum, item) {
            return sum + item.quantity;
        }, 0);

    if (cartCounter) {
        cartCounter.textContent =
            totalQuantity;
    }

    renderCart();

}


function renderCart() {

    const cartItems =
        $("#cartItems");

    const cartTotal =
        $("#cartTotal");

    if (!cartItems) return;

    if (cart.length === 0) {

        cartItems.innerHTML = `
            <div class="empty-cart">

                <i class="fa-solid fa-cart-shopping"></i>

                <h3>Savatcha bo‘sh</h3>

                <p>
                    Mahsulot qo‘shsangiz shu yerda ko‘rinadi.
                </p>

            </div>
        `;

        if (cartTotal) {
            cartTotal.textContent = "0 so‘m";
        }

        return;
    }

    cartItems.innerHTML = "";

    let total = 0;

    cart.forEach(function (item, index) {

        total +=
            item.price * item.quantity;

        const div =
            document.createElement("div");

        div.style.cssText = `
            display:flex;
            align-items:center;
            gap:12px;
            padding:12px 0;
            border-bottom:1px solid var(--border);
        `;

        div.innerHTML = `

            <div style="
                width:45px;
                height:45px;
                display:flex;
                align-items:center;
                justify-content:center;
                border-radius:10px;
                background:#eef2ff;
                color:#6366f1;
            ">

                <i class="fa-solid fa-box"></i>

            </div>

            <div style="flex:1">

                <strong>
                    ${item.name}
                </strong>

                <div style="
                    color:#64748b;
                    font-size:12px;
                ">

                    ${item.quantity} ×
                    ${formatPrice(item.price)}

                </div>

            </div>

            <button
                class="remove-cart"
                data-index="${index}"
                style="
                    background:none;
                    color:#ef4444;
                    cursor:pointer;
                "
            >

                <i class="fa-solid fa-trash"></i>

            </button>

        `;

        cartItems.appendChild(div);

    });

    if (cartTotal) {
        cartTotal.textContent =
            formatPrice(total);
    }

    $$(".remove-cart").forEach(function (button) {

        button.addEventListener("click", function () {

            const index =
                Number(button.dataset.index);

            cart.splice(index, 1);

            saveCart();

            showNotification(
                "Savatcha",
                "Mahsulot o‘chirildi."
            );

        });

    });

}


function formatPrice(price) {

    return new Intl.NumberFormat("uz-UZ")
        .format(price) + " so‘m";

}


/* =====================================================
   19. SAVATCHAGA QO‘SHISH
===================================================== */

$$(".add-cart-button").forEach(function (button) {

    button.addEventListener("click", function () {

        const product =
            button.closest(".product-card");

        if (!product) return;

        const nameElement =
            product.querySelector("h3");

        const priceElement =
            product.querySelector(
                ".product-bottom strong"
            );

        if (!nameElement || !priceElement) return;

        const name =
            nameElement.textContent.trim();

        const price =
            Number(
                priceElement.textContent
                    .replace(/[^\d]/g, "")
            );

        const existing =
            cart.find(function (item) {
                return item.name === name;
            });

        if (existing) {

            existing.quantity++;

        } else {

            cart.push({

                name: name,

                price: price,

                quantity: 1

            });

        }

        saveCart();

        showNotification(
            "Savatcha",
            name + " savatchaga qo‘shildi."
        );

    });

});


/* =====================================================
   20. SAVATCHA OYNASI
===================================================== */

const cartButton = $("#cartButton");

if (cartButton) {

    cartButton.addEventListener("click", function () {

        updateCart();

        openModal("cartModal");

    });

}

updateCart();


/* =====================================================
   21. TIL TANLASH
===================================================== */

$$(".language-menu button").forEach(function (button) {

    button.addEventListener("click", function () {

        const language =
            button.dataset.language ||
            button.textContent.trim();

        localStorage.setItem(
            "language",
            language
        );

        showNotification(
            "Til",
            "Tanlangan til: " + language
        );

    });

});


/* =====================================================
   22. ALOQA FORMASI
===================================================== */

const contactForm = $("#contactForm");

if (contactForm) {

    contactForm.addEventListener("submit", function (event) {

        event.preventDefault();

        const name =
            contactForm.querySelector(
                'input[name="name"]'
            );

        const email =
            contactForm.querySelector(
                'input[name="email"]'
            );

        const message =
            contactForm.querySelector(
                "textarea"
            );

        if (
            !name ||
            !email ||
            !message ||
            name.value.trim() === "" ||
            email.value.trim() === "" ||
            message.value.trim() === ""
        ) {

            showNotification(
                "Xatolik",
                "Barcha maydonlarni to‘ldiring."
            );

            return;
        }

        showNotification(
            "Yuborildi",
            "Xabaringiz muvaffaqiyatli yuborildi."
        );

        contactForm.reset();

    });

}


/* =====================================================
   23. NAVIGATION
===================================================== */

$$(".nav-links a").forEach(function (link) {

    link.addEventListener("click", function () {

        $$(".nav-links a").forEach(function (item) {

            item.classList.remove("active");

        });

        link.classList.add("active");

    });

});


/* =====================================================
   24. BILDIRISHNOMA
===================================================== */

function showNotification(title, message) {

    const notification =
        $("#notification");

    if (!notification) {

        alert(title + "\n" + message);

        return;

    }

    const titleElement =
        notification.querySelector("strong");

    const messageElement =
        notification.querySelector("p");

    if (titleElement) {
        titleElement.textContent = title;
    }

    if (messageElement) {
        messageElement.textContent = message;
    }

    notification.classList.add("active");

    setTimeout(function () {

        notification.classList.remove("active");

    }, 3000);

}


const notificationClose =
    $("#notificationClose");

if (notificationClose) {

    notificationClose.addEventListener(
        "click",
        function () {

            $("#notification")
                .classList.remove("active");

        }
    );

}


/* =====================================================
   25. FOOTER YILI
===================================================== */

const currentYear =
    $("#currentYear");

if (currentYear) {

    currentYear.textContent =
        new Date().getFullYear();

}


/* =====================================================
   26. SAYT YUKLANGANDA
===================================================== */

window.addEventListener("load", function () {

    document.body.classList.add("loaded");

    console.log(
        "E-commerce sayt JavaScript orqali ishga tushdi."
    );

});
```
/* =========================================
   27. MYSQL BAZASIDAN MAHSULOTLARNI YUKLASH
========================================= */

async function loadDatabaseProducts() {
    const container = document.getElementById("productsContainer");

    if (!container) return;

    container.innerHTML = "<p>Mahsulotlar yuklanmoqda...</p>";

    try {
        const response = await fetch("products_api.php");

        if (!response.ok) {
            throw new Error("Serverdan javob olishda xatolik.");
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Mahsulotlarni yuklab bo'lmadi.");
        }

        const products = result.products;

        if (!products.length) {
            container.innerHTML =
                "<p>Hozircha mahsulotlar mavjud emas.</p>";
            return;
        }

        container.innerHTML = "";

        products.forEach(function (product) {
            const card = document.createElement("article");
            card.className = "product-card";
            card.dataset.id = String(product.id);
            card.dataset.name = product.name;
            card.dataset.category = product.category || "";

            const price = Number(product.price);
            const oldPrice = product.old_price
                ? Number(product.old_price)
                : null;

            const hasDiscount =
                oldPrice !== null && oldPrice > price;

            const imageHTML = product.image
                ? `<img
                    src="${escapeProductHTML(product.image)}"
                    alt="${escapeProductHTML(product.name)}"
                    style="width:100%;height:100%;object-fit:contain;"
                   >`
                : `<div class="product-placeholder">
                       <i class="fa-solid fa-box"></i>
                   </div>`;

            const isFavorite = favorites.includes(String(product.id));

            card.innerHTML = `
                <div class="product-image">

                    ${
                        hasDiscount
                            ? '<span class="product-badge sale">Chegirma</span>'
                            : '<span class="product-badge">Yangi</span>'
                    }

                    <button type="button"
                        class="favorite-button ${isFavorite ? "active" : ""}"
                        aria-label="Sevimlilarga qo'shish">
                        <i class="${isFavorite
                            ? "fa-solid"
                            : "fa-regular"} fa-heart"></i>
                    </button>

                    ${imageHTML}
                </div>

                <div class="product-info">
                    <span class="product-category">
                        ${escapeProductHTML(product.category || "Boshqa")}
                    </span>

                    <h3>${escapeProductHTML(product.name)}</h3>

                    <p class="product-stock">
                        ${Number(product.stock) > 0
                            ? "Mavjud: " + Number(product.stock) + " dona"
                            : "Hozirda mavjud emas"}
                    </p>

                    <div class="product-bottom">
                        <div>
                            <strong>${formatPrice(price)}</strong>

                            ${
                                hasDiscount
                                    ? `<del>${formatPrice(oldPrice)}</del>`
                                    : ""
                            }
                        </div>

                        <button
                            type="button"
                            class="add-cart-button"
                            aria-label="Savatchaga qo'shish"
                            ${Number(product.stock) <= 0 ? "disabled" : ""}>
                            <i class="fa-solid fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(card);
        });

    } catch (error) {
        console.error("Mahsulotlarni yuklash xatosi:", error);

        container.innerHTML =
            "<p>Mahsulotlarni yuklab bo'lmadi. " +
            "products_api.php va ma'lumotlar bazasini tekshiring.</p>";
    }
}

/* HTML ichiga chiqariladigan matnni xavfsizlantirish */
function escapeProductHTML(value) {
    return String(value ?? "").replace(/[&<>"']/g, function (char) {
        const entities = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        };

        return entities[char];
    });
}

/* Dinamik mahsulotlar uchun tugmalar */
const databaseProductsContainer =
    document.getElementById("productsContainer");

if (databaseProductsContainer) {
    databaseProductsContainer.addEventListener("click", function (event) {
        const card = event.target.closest(".product-card");

        if (!card || !databaseProductsContainer.contains(card)) {
            return;
        }

        const favoriteButton =
            event.target.closest(".favorite-button");

        const cartButton =
            event.target.closest(".add-cart-button");

        const productId = String(card.dataset.id);
        const name = card.querySelector("h3").textContent.trim();

        const priceElement =
            card.querySelector(".product-bottom strong");

        const price = Number(
            priceElement.textContent.replace(/[^\d]/g, "")
        );

        /* Sevimlilar */
        if (favoriteButton) {
            if (favorites.includes(productId)) {
                favorites = favorites.filter(function (id) {
                    return id !== productId;
                });

                favoriteButton.classList.remove("active");
                favoriteButton.innerHTML =
                    '<i class="fa-regular fa-heart"></i>';

                showNotification(
                    "Sevimlilar",
                    "Mahsulot sevimlilardan olib tashlandi."
                );
            } else {
                favorites.push(productId);

                favoriteButton.classList.add("active");
                favoriteButton.innerHTML =
                    '<i class="fa-solid fa-heart"></i>';

                showNotification(
                    "Sevimlilar",
                    "Mahsulot sevimlilarga qo'shildi."
                );
            }

            localStorage.setItem(
                "favorites",
                JSON.stringify(favorites)
            );

            updateFavoriteCounter();
            return;
        }

        /* Savatcha */
        if (cartButton) {
            if (cartButton.disabled) {
                showNotification(
                    "Mahsulot mavjud emas",
                    "Bu mahsulot hozirda omborda yo'q."
                );
                return;
            }

            const existing = cart.find(function (item) {
                return String(item.id) === productId;
            });

            if (existing) {
                existing.quantity++;
            } else {
                cart.push({
                    id: productId,
                    name: name,
                    price: price,
                    quantity: 1
                });
            }

            saveCart();

            showNotification(
                "Savatcha",
                name + " savatchaga qo'shildi."
            );
        }
    });
}

/* Sahifa ochilganda mahsulotlarni yuklash */
loadDatabaseProducts();