export function toFormData(data: any) {
    const fd = new FormData();

    Object.entries(data).forEach(([key, value]) => {
        if (value === null || value === undefined) {
            return;
        }

        if (value instanceof File) {
            fd.append(key, value);
        } else if (Array.isArray(value)) {
            value.forEach((v, i) => {
                fd.append(`${key}[${i}]`, v);
            });
        } else if (typeof value === 'object') {
            fd.append(key, JSON.stringify(value));
        } else {
            fd.append(key, String(value));
        }
    });

    return fd;
}