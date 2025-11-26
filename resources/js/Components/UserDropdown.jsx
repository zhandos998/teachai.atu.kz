import { useState, useEffect, useRef } from "react";
import {
    Cog6ToothIcon,
    ArrowLeftOnRectangleIcon,
} from "@heroicons/react/24/outline";

export default function UserDropdown({ auth, logout, sidebarOpen }) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    /* Закрывать dropdown если sidebar закрыт */
    useEffect(() => {
        if (!sidebarOpen) setOpen(false);
    }, [sidebarOpen]);

    /* Закрытие dropdown по клику вне */
    useEffect(() => {
        function handleClickOutside(e) {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(e.target)
            ) {
                setOpen(false);
            }
        }

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    return (
        <div
            className="mt-auto border-t border-[#f7f8fc] pt-4 relative select-none user-dropdown-area"
            ref={dropdownRef}
        >
            {/* USER BLOCK */}
            <button
                onClick={() => setOpen(!open)}
                className={
                    "flex items-center w-full rounded-lg hover:bg-[#1A2E67] transition-all text-left " +
                    (sidebarOpen ? "px-3 py-2 space-x-3" : "px-1 py-2")
                }
            >
                {/* AVATAR */}
                <div className="h-10 w-10 rounded-full bg-[#f7f8fc] flex items-center justify-center text-[#1A2E67] font-semibold">
                    {auth?.user?.name?.[0]}
                </div>

                {/* NAME + EMAIL WITH ANIMATION */}
                <div
                    className={
                        "flex flex-col overflow-hidden whitespace-nowrap transition-all duration-300 text-gray-200 " +
                        (sidebarOpen
                            ? "opacity-100 w-auto ml-3"
                            : "opacity-0 w-0 ml-0")
                    }
                >
                    <span className="font-medium leading-tight">
                        {auth?.user?.name}
                    </span>
                    <span className="text-gray-400 text-sm leading-tight">
                        {auth?.user?.email}
                    </span>
                </div>
            </button>

            {/* DROPDOWN */}
            {open && sidebarOpen && (
                <div className="absolute left-3 right-3 bottom-14 bg-[#14224C] text-gray-200 shadow-lg rounded-xl py-1 border border-[#1A2E67] animate-fadeIn">
                    <a
                        href="/profile"
                        className="group flex items-center space-x-3 px-3 py-2 hover:bg-[#1A2E67] rounded-md transition"
                    >
                        <Cog6ToothIcon className="h-5 w-5 text-gray-400 group-hover:text-gray-200 transition" />
                        <span>Профиль</span>
                    </a>
                    <a
                        href="/admin"
                        className="group flex items-center space-x-3 px-3 py-2 hover:bg-[#1A2E67] rounded-md transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            strokeWidth={1.5}
                            stroke="currentColor"
                            className="h-5 w-5 text-gray-400 group-hover:text-gray-200 transition"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                            />
                        </svg>

                        <span>Админ-панель</span>
                    </a>

                    <button
                        onClick={logout}
                        className="group flex items-center space-x-3 px-3 py-2 w-full text-left text-red-400 hover:bg-[#1A2E67] rounded-md transition"
                    >
                        <ArrowLeftOnRectangleIcon className="h-5 w-5 group-hover:text-red-300 transition" />
                        <span>Выйти</span>
                    </button>
                </div>
            )}
        </div>
    );
}
