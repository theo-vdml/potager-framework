import { defineConfig } from 'vitepress';

// https://vitepress.dev/reference/site-config
export default defineConfig({
	title: 'Potager Framework',
	description: 'A small MVC PHP Framework',
	themeConfig: {
		// https://vitepress.dev/reference/default-theme-config
		nav: [
			{ text: 'Home', link: '/' },
			{ text: 'Documentation', link: 'guide//getting-started' },
		],

		sidebar: [
			{
				text: 'Guide',
				items: [{ text: 'Getting Started', link: '/guide/getting-started' }],
			},
			{
				text: 'Basics',
				items: [
					{ text: 'Models', link: '/basics/models' },
					{ text: 'CRUD Operations', link: '/basics/crud-operations' },
				],
			},
		],

		socialLinks: [{ icon: 'github', link: 'https://github.com/vuejs/vitepress' }],
	},
});
