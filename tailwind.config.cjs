/** Static Tailwind CSS build for BizStory; keep classes generated in client JS. */
module.exports = {
  content: ["./index.html", "./industry.html", "./privacy.html", "./assets/*.js"],
  theme: { extend: {} },
  safelist: [
    "border-[#063b78]", "border-slate-200", "grid-cols-1", "sm:grid-cols-2",
    "xl:grid-cols-5", "bg-green-100", "text-green-700", "bg-amber-100",
    "text-amber-700", "bg-slate-50", "text-slate-600", "bg-blue-700",
    "text-white", "p-5", "rounded-2xl", "mt-7", "w-full", "py-4",
    "font-bold", "text-center", "text-3xl", "flex", "flex-wrap"
  ],
  plugins: []
};
