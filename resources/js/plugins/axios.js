import axios from 'axios';

const axiosInstance = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request Interceptor
axiosInstance.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve(token);
        }
    });
    failedQueue = [];
};

// Response Interceptor
axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        const originalRequest = error.config;

        if (error.response && error.response.status === 401 && originalRequest) {
            // Avoid infinite loops if login or refresh itself fails
            if (originalRequest.url.includes('/auth/login') || originalRequest.url.includes('/auth/refresh')) {
                localStorage.removeItem('token');
                window.location.href = '/login';
                return Promise.reject(error);
            }

            if (!originalRequest._retry) {
                if (isRefreshing) {
                    return new Promise(function(resolve, reject) {
                        failedQueue.push({resolve, reject})
                    }).then(token => {
                        originalRequest.headers['Authorization'] = 'Bearer ' + token;
                        return axiosInstance(originalRequest);
                    }).catch(err => {
                        return Promise.reject(err);
                    });
                }

                originalRequest._retry = true;
                isRefreshing = true;

                return new Promise(function (resolve, reject) {
                    axios.post('/api/auth/refresh', {}, {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token')
                        }
                    })
                    .then(({data}) => {
                        const token = data.access_token || data.token; // Handle standard JWT responses
                        if (token) {
                            localStorage.setItem('token', token);
                            axiosInstance.defaults.headers.common['Authorization'] = 'Bearer ' + token;
                            originalRequest.headers['Authorization'] = 'Bearer ' + token;
                            processQueue(null, token);
                            resolve(axiosInstance(originalRequest));
                        } else {
                            throw new Error('Token not found in refresh response');
                        }
                    })
                    .catch((err) => {
                        processQueue(err, null);
                        localStorage.removeItem('token');
                        window.location.href = '/login';
                        reject(err);
                    })
                    .finally(() => {
                        isRefreshing = false;
                    });
                });
            }
        }
        return Promise.reject(error);
    }
);

export default axiosInstance;
