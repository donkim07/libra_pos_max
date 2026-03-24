// document.addEventListener('DOMContentLoaded', () => {
//     // Wait for Alpine to initialize
//     if (typeof Alpine !== 'undefined') {
//         // Override the sidebar store initialization
//         const originalStore = Alpine.store('sidebar');

//         // Reset collapsed groups on page load
//         const sidebarGroups = document.querySelectorAll('.fi-sidebar-group');
//         const collapsedGroups = [];

//         sidebarGroups.forEach(group => {
//             // Check if this group has an active child
//             const hasActiveChild = group.querySelector('.fi-sidebar-item-active');

//             // Get the group label from the button
//             const button = group.querySelector('.fi-sidebar-group-collapse-btn');
//             const label = button?.closest('[x-data]')?._x_dataStack?.[0]?.label;

//             // If no active child, add to collapsed groups
//             if (!hasActiveChild && label) {
//                 collapsedGroups.push(label);
//             }
//         });

//         // Update the store
//         if (originalStore) {
//             originalStore.collapsedGroups = collapsedGroups;
//         }
//     }
// });







// document.addEventListener('DOMContentLoaded', function() {
//     // Refresh CSRF token before any logout action
//     document.addEventListener('livewire:init', () => {
//         Livewire.hook('request', ({ fail }) => {
//             fail(({ status, preventDefault }) => {
//                 if (status === 419) {
//                     // Refresh the page to get a new CSRF token
//                     window.location.reload();
//                     preventDefault();
//                 }
//             });
//         });
//     });
// });
