import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router, usePage } from "@inertiajs/react";
import { useState, useRef, useEffect } from "react";
import axios from "axios";

export default function Dashboard() {
    const [isThinking, setIsThinking] = useState(false);
    const messagesEndRef = useRef(null);
    const { props } = usePage();
    const answer = props.chat_answer;
    const [message, setMessage] = useState(""); // строка ввода
    const [messages, setMessages] = useState([]); // массив сообщений
    const [currentChat, setCurrentChat] = useState(null);
    const { url } = usePage();
    const { chat, messages: initialMessages } = usePage().props;

    useEffect(() => {
        if (chat) {
            setCurrentChat(chat);
            setMessages(initialMessages);
        }
    }, [chat]);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [messages]);

    useEffect(() => {
        if (url === "/") {
            setMessages([]);
            setCurrentChat(null);
            setMessage("");
        }
    }, [url]);

    const sendMessage = async () => {
        if (isThinking) return;
        if (!message.trim()) return;

        setIsThinking(true);

        let chatId = currentChat?.id;

        // 1) ЕСЛИ НЕТ ЧАТА → создаем новый как ChatGPT
        if (!currentChat) {
            const res = await axios.post("/chat/new");
            chatId = res.data.chat.id;
            setCurrentChat(res.data.chat);

            router.visit(`/chat/${chatId}`, {
                preserveState: true,
                preserveScroll: true,
            });
        }

        // 2) Показываем сообщение пользователя сразу
        setMessages((prev) => [...prev, { role: "user", content: message }]);

        // 3) Отправляем сообщение в API
        const response = await axios.post("/chat/send", {
            chat_id: chatId,
            message,
        });

        // 4) Добавляем ответ ассистента
        setMessages((prev) => [
            ...prev,
            { role: "assistant", content: response.data.answer },
        ]);

        setMessage("");
        setIsThinking(false);
    };

    return (
        <AuthenticatedLayout
            header={<></>} // убираем верхний заголовок, как в ChatGPT
        >
            <Head title="TeachAI Chat" />

            {/* CONTAINER */}
            <div className="relative h-[calc(100vh-0px)] w-full">
                {/* ===== TITLE (центр экрана) ===== */}
                <div
                    className={
                        "absolute left-1/2 transform -translate-x-1/2 text-center transition-all duration-500 " +
                        (messages.length === 0
                            ? "top-[22%] opacity-100"
                            : "top-[5%] opacity-0")
                    }
                >
                    <h1
                        className={
                            "aurora-title text-2xl md:text-4xl text-center " +
                            (messages.length === 0
                                ? "opacity-100"
                                : "opacity-0")
                        }
                    >
                        Добро пожаловать в <br />
                        <span className="aurora-text font-extrabold text-4xl md:text-6xl md:leading-[2.0]">
                            TeachAI
                        </span>
                    </h1>
                </div>

                {/* CHAT WINDOW */}
                <div
                    className="absolute left-1/2 transform -translate-x-1/2 w-full px-4
                overflow-y-auto space-y-4"
                    style={{
                        top: "0%",
                        bottom: "0%",
                        // paddingBottom: "150px",
                        marginBottom: "110px",
                        paddingTop: "50px",
                        paddingLeft: "400px",
                        paddingRight: "400px",
                        position: "absolute",
                    }}
                >
                    {messages.map((msg, index) => (
                        <div
                            key={index}
                            className={
                                msg.role === "user" ? "text-right" : "text-left"
                            }
                        >
                            <div
                                className={
                                    "inline-block px-4 py-2 rounded-xl shadow-md max-w-[80%] " +
                                    (msg.role === "user"
                                        ? "bg-[#21397D] text-white"
                                        : "bg-gray-200 text-gray-800")
                                }
                            >
                                {msg.content}
                            </div>
                        </div>
                    ))}

                    {isThinking && (
                        <div className="w-full flex justify-center py-2">
                            <div className="flex flex-row gap-2">
                                <div className="w-3 h-3 rounded-full bg-[#21397D] animate-bounce"></div>
                                <div className="w-3 h-3 rounded-full bg-[#21397D] animate-bounce [animation-delay:-.2s]"></div>
                                <div className="w-3 h-3 rounded-full bg-[#21397D] animate-bounce [animation-delay:-.4s]"></div>
                                <div className="w-3 h-3 rounded-full bg-[#21397D] animate-bounce [animation-delay:-.6s]"></div>
                            </div>
                        </div>
                    )}

                    <div ref={messagesEndRef}></div>
                </div>
                {/* ===== INPUT BOX ===== */}
                <div
                    className={
                        "absolute left-1/2 transform -translate-x-1/2 w-full transition-all duration-500 " +
                        (messages.length === 0 ? "top-[40%]" : "bottom-6")
                    }
                    style={{
                        // paddingBottom: "150px",
                        // marginBottom: "110px",
                        paddingLeft: "400px",
                        paddingRight: "400px",
                    }}
                >
                    {answer && (
                        <div className="mt-6 max-w-3xl mx-auto bg-white shadow rounded-xl p-6 text-gray-800">
                            <p>{answer}</p>
                        </div>
                    )}
                    <div className="flex items-center bg-white shadow-lg rounded-full px-2 py-1 border border-gray-200">
                        <input
                            type="text"
                            placeholder="Напишите сообщение..."
                            value={message}
                            onChange={(e) => setMessage(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === "Enter" && !isThinking) {
                                    e.preventDefault();
                                    sendMessage();
                                }
                            }}
                            className="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 focus:border-none text-gray-800 text-lg"
                        />

                        <button
                            disabled={isThinking}
                            onClick={sendMessage}
                            className={
                                "flex items-center justify-center h-10 w-10 rounded-full transition shadow-md " +
                                (isThinking
                                    ? "bg-gray-400 cursor-not-allowed"
                                    : "bg-[#21397D] hover:bg-[#2A4A9A]")
                            }
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth={2}
                                stroke="white"
                                className="w-5 h-5"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
