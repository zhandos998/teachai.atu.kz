import React from "react";
import { Link, usePage, router } from "@inertiajs/react";

import { useState, useEffect } from "react";
import {
    Bars3Icon,
    ChatBubbleBottomCenterTextIcon,
    Cog6ToothIcon,
    HomeIcon,
    ArrowLeftOnRectangleIcon,
} from "@heroicons/react/24/outline";
import UserDropdown from "@/Components/UserDropdown";

export default function AppLayout({ children }) {
    const { auth } = usePage().props;
    // const roles = auth.user ? auth.user.roles : [];
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [chats, setChats] = useState([]);
    // const [messages, setMessages] = useState([]); // массив сообщений

    useEffect(() => {
        axios.get("/chats").then((res) => setChats(res.data.chats));
    }, []);

    // const loadChat = (id) => {
    //     axios.get(`/chat/${id}`).then((res) => {
    //         setCurrentChat(res.data.chat);
    //         // setMessages(res.data.messages);
    //     });
    // };

    const deleteChat = (id) => {
        axios.delete(`/chat/${id}`).then(() => {
            setChats(chats.filter((c) => c.id !== id));
            if (currentChat?.id === id) {
                setCurrentChat(null);
                setMessages([]); // <== добавить
            }
        });
    };

    // const createChat = () => {
    //     axios.post("/chat/new").then((res) => {
    //         setChats([...chats, res.data.chat]);
    //         loadChat(res.data.chat.id);
    //     });
    // };

    const logout = (e) => {
        e.preventDefault();
        router.post("/logout");
    };

    return (
        <div className="flex h-screen bg-[#f7f8fc]">
            {/* SIDEBAR */}
            <aside
                className={
                    "bg-[#21397D] text-gray-100 flex flex-col transition-all duration-300 py-6 px-4 " +
                    (sidebarOpen
                        ? "translate-x-0 w-64"
                        : "-translate-x-[0%] w-20")
                }
            >
                <div className="flex items-center justify-between px-2 mb-6">
                    <div
                        className={
                            "flex items-center transition-all duration-300 " +
                            (sidebarOpen ? "space-x-2" : "")
                        }
                    >
                        <Bars3Icon
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="h-6 w-6 text-gray-200 cursor-pointer hover:text-white transition-all duration-300 "
                        />

                        {/* <span className="aurora-text font-extrabold text-4xl md:text-6xl md:leading-[2.0]"></span> */}
                        <span
                            className={
                                "aurora-text text-xl font-semibold overflow-hidden transition-all duration-300 " +
                                (sidebarOpen
                                    ? "opacity-100 w-auto ml-2"
                                    : "opacity-0 w-0 ml-0")
                            }
                        >
                            TeachAI
                        </span>
                    </div>
                </div>

                <nav className="flex flex-col space-y-1">
                    <Link
                        href="/"
                        className={
                            "flex items-center px-3 py-2 rounded-lg hover:bg-[#14224C] transition " +
                            (sidebarOpen ? "space-x-2" : "")
                        }
                    >
                        <HomeIcon className="h-5 w-5 text-gray-100" />

                        <span
                            className={
                                "overflow-hidden whitespace-nowrap transition-all duration-300 " +
                                (sidebarOpen
                                    ? "opacity-100 w-auto"
                                    : "opacity-0 w-0")
                            }
                        >
                            Главная
                        </span>
                    </Link>

                    <Link
                        // onClick={createChat}
                        href={"/"}
                        className={
                            "flex items-center px-3 py-2 rounded-lg hover:bg-[#14224C] transition " +
                            (sidebarOpen ? "space-x-2" : "")
                        }
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

                        <span
                            className={
                                "overflow-hidden whitespace-nowrap transition-all duration-300 " +
                                (sidebarOpen
                                    ? "opacity-100 w-auto"
                                    : "opacity-0 w-0")
                            }
                        >
                            Новый чат
                        </span>
                    </Link>

                    {chats.map((chat) => (
                        <div
                            key={chat.id}
                            className={
                                "flex items-center justify-between px-3 py-2 rounded-lg hover:bg-[#14224C] transition " +
                                (sidebarOpen ? "space-x-2" : "")
                            }
                        >
                            {" "}
                            <Link
                                // onClick={() => loadChat(chat.id)}
                                href={"/chat/" + chat.id}
                                className="flex items-center flex-1 text-left"
                            >
                                {" "}
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
                                </svg>{" "}
                                <span
                                    className={
                                        "ml-2 overflow-hidden whitespace-nowrap transition-all duration-300 " +
                                        (sidebarOpen
                                            ? "opacity-100 w-auto"
                                            : "opacity-0 w-0")
                                    }
                                >
                                    {" "}
                                    Chat #{chat.id}{" "}
                                </span>{" "}
                            </Link>{" "}
                            {sidebarOpen && (
                                <button
                                    onClick={() => deleteChat(chat.id)}
                                    className="text-red-300 hover:text-red-500 transition"
                                >
                                    {" "}
                                    🗑{" "}
                                </button>
                            )}{" "}
                        </div>
                    ))}
                </nav>

                <UserDropdown
                    auth={auth}
                    logout={logout}
                    sidebarOpen={sidebarOpen}
                />
            </aside>

            {/* MAIN CONTENT */}
            <main className="flex-1 overflow-y-auto">{children}</main>

            {/* <main className="flex-1 overflow-y-auto">
                {React.cloneElement(children, {
                    chats,
                    currentChat,
                    messages,
                    loadChat,
                    createChat,
                    setMessages,
                })}
            </main> */}
        </div>
    );
}
