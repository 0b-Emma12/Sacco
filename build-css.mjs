// Build-time only: compiles resources/css/app.css into public/css/app.css.
// The browser receives plain CSS; no JavaScript is shipped to the client.
import fs from "node:fs";
import path from "node:path";
import { compile } from "tailwindcss";

const root = process.cwd();
const viewsDir = path.join(root, "resources", "views");

function walk(dir, out = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full, out);
    else if (entry.name.endsWith(".blade.php")) out.push(full);
  }
  return out;
}

const candidates = new Set();

function addTokens(chunk) {
  for (const raw of chunk.split(/\s+/)) {
    const token = raw.trim();
    if (!token || token.length > 100) continue;
    if (/[^A-Za-z0-9_\-\[\]#/.%&!:*+~]/.test(token)) continue;
    if (!/^[!-]?[A-Za-z0-9]/.test(token)) continue;
    candidates.add(token);
  }
}

function scan(text) {
  for (const match of text.matchAll(/class\s*=\s*"([^"]*)"/g)) {
    addTokens(
      match[1]
        .replace(/\{\{[\s\S]*?\}\}/g, " ")
        .replace(/\{!![\s\S]*?!!\}/g, " ")
    );
  }
  // Class strings assigned to PHP variables and echoed later.
  for (const match of text.matchAll(/'([^'\n]{1,400})'/g)) addTokens(match[1]);
}

for (const file of walk(viewsDir)) scan(fs.readFileSync(file, "utf8"));

const input = fs.readFileSync(path.join(root, "resources", "css", "app.css"), "utf8");

const compiler = await compile(input, {
  base: root,
  loadStylesheet: async (id, base) => {
    const file =
      id === "tailwindcss"
        ? path.join(root, "node_modules", "tailwindcss", "index.css")
        : path.resolve(base, id);
    return {
      path: file,
      base: path.dirname(file),
      content: fs.readFileSync(file, "utf8"),
    };
  },
});

const output = compiler.build([...candidates]);
fs.mkdirSync(path.join(root, "public", "css"), { recursive: true });
fs.writeFileSync(path.join(root, "public", "css", "app.css"), output);

console.log(
  `Built public/css/app.css (${(output.length / 1024).toFixed(1)} kB) from ${candidates.size} candidate classes.`
);
