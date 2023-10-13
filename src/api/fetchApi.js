const fetchApi = (inputData) => {
    return fetch('https://fmc2.avmg.com.ua/study/nakoskin/seabattle/server.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(inputData),
    })
        .then(response => response.json())
        .catch(error => {
            console.error('error:', error);
            throw error;
        });
}

export {fetchApi}
