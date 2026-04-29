/** @type {import("prettier").Config} */
export default {
	printWidth: 100,
	tabWidth: 4,
	useTabs: true, // IMPORTANT : Prettier utilise des spaces même si editorconfig = tab
	semi: true,
	singleQuote: true,
	quoteProps: "as-needed",
	trailingComma: "all",
	bracketSpacing: true,
	arrowParens: "always",

	endOfLine: "lf",

	overrides: [
		{
			files: "*.md",
			options: {
				proseWrap: "preserve",
			},
		},
		{
			files: "*.scss",
			options:{
				singleQuote: false,
			},
		},
	],
};
