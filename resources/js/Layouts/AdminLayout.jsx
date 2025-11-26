import React from "react";
import { Link, usePage, router } from "@inertiajs/react";
import ApplicationLogo from "@/Components/ApplicationLogo";

export default function AppLayout({ children }) {
    const { auth } = usePage().props;
    const roles = auth.user ? auth.user.roles : [];

    const [menuOpen, setMenuOpen] = React.useState(false);

    const logout = (e) => {
        e.preventDefault();
        router.post("/logout");
    };

    return (
        <div className="h-screen flex flex-col overflow-hidden bg-gray-100">
            {/* Шапка */}
            <header
                className="fixed top-0 left-0 w-full shadow z-50 text-white"
                style={{ backgroundColor: "#21397D" }}
            >
                <div className="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                    {/* ЛЕВАЯ ЧАСТЬ: ЛОГО + МЕНЮ */}
                    <div className="flex items-center space-x-10">
                        {/* Logo */}
                        <Link href="/">
                            <ApplicationLogo className="h-16 w-16 fill-current text-gray-200" />
                        </Link>

                        {/* Navigation — СЛЕВА */}
                        <nav className="flex space-x-6 text-sm font-medium">
                            <Link
                                href="/admin"
                                className="hover:text-gray-300 transition"
                            >
                                Главная
                            </Link>

                            <Link
                                href="/admin/documents"
                                className="hover:text-gray-300 transition"
                            >
                                Документы
                            </Link>
                        </nav>
                    </div>

                    {/* ПРАВАЯ ЧАСТЬ: ПРОФИЛЬ */}
                    {auth?.user && (
                        <div className="relative">
                            <button
                                onClick={() => setMenuOpen(!menuOpen)}
                                className="text-sm font-medium hover:text-gray-200"
                            >
                                {auth.user.name} ▾
                            </button>

                            {menuOpen && (
                                <div className="absolute right-0 mt-2 w-40 bg-white border shadow-lg rounded-md py-1 z-50">
                                    <Link
                                        href="/profile"
                                        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Мой профиль
                                    </Link>

                                    <form onSubmit={logout}>
                                        <button
                                            type="submit"
                                            className="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                        >
                                            Выйти
                                        </button>
                                    </form>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </header>

            {/* Контент */}
            <main
                className="flex-1 overflow-y-auto px-4 py-6"
                style={{ height: "calc(100vh - 80px)" }}
            >
                <div className="max-w-7xl mx-auto w-full mt-20">{children}</div>
            </main>

            {/* Футер */}
            <footer
                className="bg-white border-t"
                style={{ backgroundColor: "#21397D" }}
            >
                <div className="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-gray-200">
                    © {new Date().getFullYear()} teachai.atu.kz — Все права
                    защищены.
                </div>
            </footer>
        </div>
    );
}
