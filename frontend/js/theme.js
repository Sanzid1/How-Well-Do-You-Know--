// Function to set theme
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // Update toggle switch
    const toggleSwitch = document.getElementById('theme-toggle');
    if (toggleSwitch) {
        toggleSwitch.checked = theme === 'dark';
    }
}

// Function to toggle theme
function toggleTheme() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
}

// Initialize theme
document.addEventListener('DOMContentLoaded', function() {
    // Get saved theme from localStorage or use system preference
    const savedTheme = localStorage.getItem('theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    // Set initial theme
    setTheme(savedTheme);
    
    // Add event listener to toggle switch
    const toggleSwitch = document.getElementById('theme-toggle');
    if (toggleSwitch) {
        toggleSwitch.addEventListener('change', toggleTheme);
    }
});