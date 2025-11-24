import React, { useState, useEffect } from "react";
import { Link, usePage, router } from "@inertiajs/react";
import axios from "axios";

import { Bars3Icon, HomeIcon } from "@heroicons/react/24/outline";

import UserDropdown from "@/Components/UserDropdown";

export default function AppLayout({ children }) {
    const { auth } = usePage().props;

    const [sidebarOpen, setSidebarOpen] = useState(false); // 📌 На мобилках закрыт
    const [isDesktop, setIsDesktop] = useState(false);
    const [chats, setChats] = useState([]);

    useEffect(() => {
        axios.get("/chats").then((res) => setChats(res.data.chats));

        const onResize = () => {
            if (window.innerWidth >= 1024) {
                setSidebarOpen(true); // ПК — всегда открыт
                setIsDesktop(true);
            } else {
                setSidebarOpen(false); // Мобилка — закрыт
                setIsDesktop(false);
            }
        };

        onResize();
        window.addEventListener("resize", onResize);
        return () => window.removeEventListener("resize", onResize);
    }, []);

    const deleteChat = (id) => {
        axios.delete(`/chat/${id}`).then(() => {
            setChats(chats.filter((c) => c.id !== id));
        });
    };

    const logout = (e) => {
        e.preventDefault();
        router.post("/logout");
    };

    return (
        <div className="flex h-screen bg-[#f7f8fc]">
            {/* TOP BAR — только на телефонах */}
            {!isDesktop && (
                <header className="fixed top-0 left-0 right-0 h-14 bg-white shadow flex items-center px-4 z-40">
                    <Bars3Icon
                        onClick={() => setSidebarOpen(true)}
                        className="h-7 w-7 text-[#21397D] cursor-pointer"
                    />
                    <h1 className="aurora-text ml-4 text-lg font-semibold text-[#21397D]">
                        TeachAI
                    </h1>
                </header>
            )}

            {/* OVERLAY — затемнение при открытом sidebar на телефоне */}
            {!isDesktop && sidebarOpen && (
                <div
                    onClick={() => setSidebarOpen(false)}
                    className="fixed inset-0 bg-black/40 z-40"
                ></div>
            )}

            {/* SIDEBAR */}
            <aside
                className={`
                    fixed lg:static top-0 left-0 h-full bg-[#21397D] text-gray-100
                    flex flex-col py-6 px-4 z-50
                    transition-transform duration-300
                    ${
                        sidebarOpen
                            ? "translate-x-0"
                            : "-translate-x-full lg:translate-x-0"
                    }
                    ${isDesktop ? "w-64" : "w-64"}
                `}
            >
                {/* HEADER */}
                <div className="flex items-center justify-between px-2 mb-6">
                    <div className="flex items-center space-x-2">
                        <Bars3Icon
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="h-6 w-6 text-gray-200 cursor-pointer"
                        />
                        <span className="aurora-text text-xl font-semibold">
                            TeachAI
                        </span>
                    </div>
                </div>

                {/* NAVIGATION */}
                <nav className="flex flex-col space-y-1">
                    <Link
                        href="/"
                        className="flex items-center px-3 py-2 rounded-lg hover:bg-[#14224C] transition space-x-2"
                    >
                        <HomeIcon className="h-5 w-5 text-gray-100" />
                        <span>Главная</span>
                    </Link>

                    {/* Новый чат */}
                    <Link
                        href="/"
                        className="flex items-center px-3 py-2 rounded-lg hover:bg-[#14224C] transition space-x-2"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            strokeWidth={1.5}
                            stroke="currentColor"
                            className="size-6"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M12 4.5v15m7.5-7.5h-15"
                            />
                        </svg>

                        <span>Новый чат</span>
                    </Link>

                    {/* Все чаты */}
                    {chats.map((chat) => (
                        <div
                            key={chat.id}
                            className="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-[#14224C] transition"
                        >
                            <Link
                                href={`/chat/${chat.id}`}
                                className="flex items-center flex-1"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth={1.5}
                                    stroke="currentColor"
                                    className="size-6 text-gray-100"
                                >
                                    {" "}
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"
                                    />{" "}
                                </svg>
                                <span className="ml-2">Chat #{chat.id}</span>
                            </Link>

                            <button
                                onClick={() => deleteChat(chat.id)}
                                className="text-red-300 hover:text-red-500 transition"
                            >
                                🗑
                            </button>
                        </div>
                    ))}
                </nav>

                <UserDropdown auth={auth} logout={logout} sidebarOpen={true} />
            </aside>

            {/* MAIN CONTENT */}
            <main
                className={`flex-1 overflow-hidden ${
                    !isDesktop ? "pt-16" : ""
                }`}
            >
                {children}
            </main>
        </div>
    );
}
