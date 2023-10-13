const parseLetterToNum = (letter) => {
    if (/^[A-J]$/.test(letter)) {
        return letter.charCodeAt(0) - "A".charCodeAt(0) + 1;
    } else {
        return null;
    }
}

export {parseLetterToNum}