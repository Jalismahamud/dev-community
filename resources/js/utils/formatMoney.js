export function formatMoney(amount) {
    const value = Number(amount ?? 0).toFixed(2);
    const [integer, decimal] = value.split('.');
    const lastThree = integer.slice(-3);
    const remaining = integer.slice(0, -3);
    const grouped = remaining
        ? `${remaining.replace(/\B(?=(\d{2})+(?!\d))/g, ',')},${lastThree}`
        : lastThree;

    return `৳ ${grouped}.${decimal}`;
}