export function setCookie(name, value, days = 365) {
    let expires = "";

    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }

    document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}${expires}; path=/`;
}

export function getCookie(name) {
    const nameEQ = encodeURIComponent(name) + "=";
    const cookies = document.cookie.split(";");

    for (let c of cookies) {
        while (c.charAt(0) === " ") c = c.substring(1);

        if (c.indexOf(nameEQ) === 0) {
            return decodeURIComponent(c.substring(nameEQ.length));
        }
    }

    return null;
}

export function deleteCookie(name) {
    document.cookie = `${encodeURIComponent(name)}=; Max-Age=0; path=/`;
}
